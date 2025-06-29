<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require 'vendor/autoload.php';
require_once '../config.php';

$db = new db();

$app = new \Slim\App;

$app->get('/event', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM events";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as &$event) {
            if ($event['productImage']) {
                $event['productImage'] = 'data:image/jpeg;base64,' . base64_encode($event['productImage']);
            }
        }

        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});


$app->get('/event/{id}', function($request, $response, $args) use($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM events WHERE eventId=:eventId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':eventId', $id);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event && $event['productImage']) {
            $event['productImage'] = 'data:image/jpeg;base64,' . base64_encode($event['productImage']);
        }

        if($event) {
            return $response->withJson($event);
        }
        else {
            return $response->withJson(["error" => "Event is not found"]);
        }
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->post('/event', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if(!isset($data['name']) || !isset($data['category']) || !isset($data['date']) || !isset($data['time']) || !isset($data['location']) || !isset($data['userId']) ) {
            throw new Exception("Data is required.");
        }
        $name = $data['name'];
        $category = $data['category'];
        $date = $data['date'];
        $time = $data['time'];
        $location = $data['location'];
        $description = $data['description'];
        $productImage = $data['productImage'];
        $userId = $data['userId'];
        $sql = "INSERT INTO events (name, category, date, time, location, description, productImage, userId)
        VALUES (:name, :category, :date, :time, :location, :description, :productImage, :userId)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':time', $time);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':productImage', $productImage);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $eventId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Event created successfully",
            "eventId" => $eventId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating user: " . $e->getMessage()]);
    }
});

$app->put('/event/{id}', function($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if(!isset($data['name']) || !isset($data['category']) || !isset($data['date']) || !isset($data['time']) || !isset($data['location']) || !isset($data['userId']) ) {
            throw new Exception("Data is required.");
        }
        $name = $data['name'];
        $category = $data['category'];
        $date = $data['date'];
        $time = $data['time'];
        $location = $data['location'];
        $description = $data['description'];
        $productImage = $data['productImage'];
        $userId = $data['userId'];
        $sql = "UPDATE events SET name = :name, category = :category, date = :date, time = :time, location = :location, description = :description, productImage = :productImage, userId = :userId  WHERE eventId = :eventId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':time', $time);
        $stmt->bindValue(':location', $location);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':productImage', $productImage);
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':eventId', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Event updated successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error updating event: " . $e->getMessage()]);
    }
});


$app->delete('/event/{id}', function($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM events WHERE eventId = :eventId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':eventId', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Event deleted successfully"]);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error deleting user: " . $e->getMessage()]);
    }
});

$app->get('/ticket', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM tickets";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});

$app->get('/ticket/{id}', function($request, $response, $args) use($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM tickets WHERE ticketId=:ticketId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':ticketId', $id);
        $stmt->execute();
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        if($ticket) {
            return $response->withJson($ticket);
        }
        else {
            return $response->withJson(["error" => "Ticket is not found"]);
        }
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->get('/ticket-event/{id}', function($request, $response, $args) use($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM tickets WHERE eventId=:eventId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':eventId', $id);
        $stmt->execute();
        $ticket = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($ticket) {
            return $response->withJson($ticket);
        }
        else {
            return $response->withJson(["error" => "Ticket is not found"]);
        }
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->post('/ticket', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if(!isset($data['name']) || !isset($data['quantity']) || !isset($data['price']) || !isset($data['startDate']) || !isset($data['startTime']) || !isset($data['endDate']) || !isset($data['endTime'])) {
            throw new Exception("Data is required.");
        }
        $name = $data['name'];
        $quantity = $data['quantity'];
        $price = $data['price'];
        $description = $data['description'];
        $startDate = $data['startDate'];
        $startTime = $data['startTime'];
        $endDate = $data['endDate'];
        $endTime = $data['endTime'];
        $eventId = $data['eventId'];
        $sql = "INSERT INTO tickets (name, quantity, price, description, startDate, startTime, endDate, endTime, eventId)
        VALUES (:name, :quantity, :price, :description, :startDate, :startTime, :endDate, :endTime, :eventId)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->bindValue(':eventId', $eventId);
        
        $stmt->execute();
        $ticketId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Ticket created successfully",
            "ticketId" => $ticketId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating user: " . $e->getMessage()]);
    }
});

$app->put('/ticket/{id}', function($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if(!isset($data['name']) || !isset($data['quantity']) || !isset($data['price']) || !isset($data['startDate']) || !isset($data['startTime']) || !isset($data['endDate']) || !isset($data['endTime'])) {
            throw new Exception("Data is required.");
        }
        $name = $data['name'];
        $quantity = $data['quantity'];
        $price = $data['price'];
        $description = $data['description'];
        $startDate = $data['startDate'];
        $startTime = $data['startTime'];
        $endDate = $data['endDate'];
        $endTime = $data['endTime'];
        $eventId = $data['eventId'];
        $sql = "UPDATE tickets SET name = :name, quantity = :quantity, price = :price, description = :description, startDate = :startDate, endDate = :endDate, endDate = :endDate, endTime = :endTime, eventId = :eventId  WHERE ticketId = :ticketId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':startTime', $startTime);
        $stmt->bindValue(':endDate', $endDate);
        $stmt->bindValue(':endTime', $endTime);
        $stmt->bindValue(':eventId', $eventId);

        $stmt->bindValue(':ticketId', $id);

        $stmt->execute();
        return $response->withJson(["message" => "Ticket updated successfully"]);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error updating ticket: " . $e->getMessage()]);
    }
});

$app->delete('/ticket/{id}', function($request, $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM tickets WHERE ticketId = :ticketId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':ticketId', $id);
        $stmt->execute();
        return $response->withJson(["message" => "Ticket deleted successfully"]);
    } catch (PDOException $e) {
        return $response->withJson(["error" => "Error deleting ticket: " . $e->getMessage()]);
    }
});


$app->get('/user/{id}', function($request, $response, $args) use($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM users WHERE userId=:userId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userId', $id);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        if($event) {
            return $response->withJson($event);
        }
        else {
            return $response->withJson(["error" => "User is not found"]);
        }
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});

$app->get('/allusers', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM users";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});

$app->post('/login', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();

        if (!isset($data['email']) || !isset($data['password'])) {
            throw new Exception("Email and password are required.");
        }

        $email = $data['email'];
        $password = $data['password'];

        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return $response->withJson(["error" => "Invalid credentials (user not found)."], 401);
        }

        if (!password_verify($password, $user['password'])) {
            return $response->withJson(["error" => "Invalid credentials (wrong password)."], 401);
        }

        unset($user['password']);

        return $response->withJson([
            "message" => "Login successful",
            "user" => $user
        ]);

    } catch (Exception $e) {
        return $response->withJson(["error" => "Login failed: " . $e->getMessage()], 500);
    }
});


$app->post('/register', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();

        if (!isset($data['fullname']) || !isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            throw new Exception("All fields are required: fullname, email, password, role.");
        }

        $fullname = $data['fullname'];
        $email = $data['email'];
        $password = $data['password'];
        $role = $data['role'];

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (fullname, email, password, role)
                VALUES (:fullname, :email, :password, :role)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':fullname', $fullname);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $hashedPassword); 
        $stmt->bindValue(':role', $role);

        $stmt->execute();
        $userId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "User created successfully",
            "userId" => $userId
        ], 201);

    } catch (Exception $e) {
        return $response->withJson([
            "error" => "Error creating user: " . $e->getMessage()
        ]);
    }
});


$app->post('/merchandise', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if(!isset($data['name']) || !isset($data['description']) || !isset($data['quantity']) || !isset($data['price'])) {
            throw new Exception("Data is required.");
        }
        $name = $data['name'];
        $description = $data['description'];
        $quantity = $data['quantity'];
        $price = $data['price'];
        $merchandiseImage = $data['merchandiseImage'];
        $eventId = $data['eventId'];
        $sql = "INSERT INTO merchandises (name, description, quantity, price, merchandiseImage, eventId)
        VALUES (:name, :description, :quantity, :price, :merchandiseImage, :eventId)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':price', $price);
        $stmt->bindValue(':merchandiseImage', $merchandiseImage);
        $stmt->bindValue(':eventId', $eventId);
        
        $stmt->execute();
        $merchandiseId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Merchandise created successfully",
            "ticketId" => $merchandiseId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating merchandise: " . $e->getMessage()]);
    }
});



$app->get('/merchandise-event/{id}', function($request, $response, $args) use($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM merchandises WHERE eventId=:eventId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':eventId', $id);
        $stmt->execute();
        $ticket = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($ticket) {
            return $response->withJson($ticket);
        }
        else {
            return $response->withJson(["error" => "Merchandises are not found"]);
        }
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Database error: " . $e->getMessage()]);
    }
});


$app->post('/order', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();

        if(!isset($data['userId']) || !isset($data['timestamp']) || !isset($data['totalAmount'])) {
            throw new Exception("Data is required.");
        }

        $userId = $data['userId'];
        $timestamp = $data['timestamp'];
        $totalAmount = $data['totalAmount'];
        $sql = "INSERT INTO orders (userId, timestamp, totalAmount)
        VALUES (:userId, :timestamp, :totalAmount)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userId', $userId);
        $stmt->bindValue(':timestamp', $timestamp);
        $stmt->bindValue(':totalAmount', $totalAmount);
        
        $stmt->execute();
        $orderId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Order created successfully",
            "orderId" => $orderId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating order: " . $e->getMessage()]);
    }
});

$app->get('/order', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $userId = $request->getQueryParams()['userId'] ?? null;

        if (!$userId) {
            throw new Exception("Missing userId");
        }

        $sql = "SELECT * FROM orders WHERE userId = :userId";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $response->withJson($orders);
    } catch(Exception $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});


$app->get('/order-ticket', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM ordertickets";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});

$app->get('/order-merchandise', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM ordermerchandises";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});


$app->post('/order-ticket', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        
        if(!isset($data['orderId']) || !isset($data['ticketId']) || !isset($data['quantity']) || !isset($data['subtotal'])) {
            throw new Exception("Data is required.");
        }

        $orderId = $data['orderId'];
        $ticketId = $data['ticketId'];
        $quantity = $data['quantity'];
        $subtotal = $data['subtotal'];
        $sql = "INSERT INTO ordertickets (orderId, ticketId, quantity, subtotal)
        VALUES (:orderId, :ticketId, :quantity, :subtotal)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':orderId', $orderId);
        $stmt->bindValue(':ticketId', $ticketId);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':subtotal', $subtotal);
        
        $stmt->execute();
        $orderTicketId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Order ticket created successfully",
            "orderTicketId" => $orderTicketId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating order ticket: " . $e->getMessage()]);
    }
});


$app->post('/order-merchandise', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        
        if(!isset($data['orderId']) || !isset($data['merchandiseId']) || !isset($data['quantity']) || !isset($data['subtotal'])) {
            throw new Exception("Data is required.");
        }

        $orderId = $data['orderId'];
        $merchandiseId = $data['merchandiseId'];
        $quantity = $data['quantity'];
        $subtotal = $data['subtotal'];
        $sql = "INSERT INTO ordermerchandises (orderId, merchandiseId, quantity, subtotal)
        VALUES (:orderId, :merchandiseId, :quantity, :subtotal)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':orderId', $orderId);
        $stmt->bindValue(':merchandiseId', $merchandiseId);
        $stmt->bindValue(':quantity', $quantity);
        $stmt->bindValue(':subtotal', $subtotal);
        
        $stmt->execute();
        $orderMerchandiseId = $conn->lastInsertId();

        return $response->withJson([
            "message" => "Order merchandise created successfully",
            "orderMerchandiseId" => $orderMerchandiseId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating order merchandise: " . $e->getMessage()]);
    }
});

$app->post('/forum', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        
        if(!isset($data['title']) || !isset($data['content'])) {
            throw new Exception("Title and mainPost are required.");
        }
        
        $title = $data['title'];
        $author = $data['author'];
        $content = $data['content'];
        
        $sql = "INSERT INTO forum_threads (title, author, content, created_at)
                VALUES (:title, :author, :content, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':author', $author);
        $stmt->bindValue(':content', $content);
        $stmt->execute();
        
        $threadId = $conn->lastInsertId();
        
        return $response->withJson([
            "message" => "Forum thread created successfully",
            "threadId" => $threadId
        ], 201);
    } catch (Exception $e) {
        return $response->withJson(["error" => "Error creating forum thread: " . $e->getMessage()], 500);
    }
});

$app->get('/forum', function($request, $response, $args) use($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM forum_threads";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $response->withJson($result);
    } catch(PDOException $e) {
        return $response->withJson(["error" => "Error: " . $e->getMessage()]);
    }
});



$app->run();


