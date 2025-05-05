<?php
/**
 * Contact Page Controller
 */

// Get database connection
$conn = getDbConnection();

// First check if the contact_messages table exists and create it if not
try {
    $checkTableStmt = $conn->prepare("
        SELECT 1 FROM contact_messages LIMIT 1
    ");
    $checkTableStmt->execute();
} catch (PDOException $e) {
    // Table doesn't exist, create it
    $sql = "
        CREATE TABLE IF NOT EXISTS `contact_messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `subject` varchar(255) NOT NULL,
          `message` text NOT NULL,
          `read` tinyint(1) NOT NULL DEFAULT 0,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $conn->exec($sql);
        error_log('Contact messages table created successfully.');
    } catch (PDOException $createErr) {
        error_log('Error creating contact_messages table: ' . $createErr->getMessage());
    }
}

// Handle form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subject is required.';
    }
    
    if (empty($message)) {
        $errors[] = 'Message is required.';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        try {
            // Begin transaction
            $conn->beginTransaction();
            
            // Insert message into database
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message, created_at) 
                VALUES (:name, :email, :subject, :message, NOW())
            ");
            
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            $result = $stmt->execute();
            
            if (!$result) {
                error_log('Error in contact form execution: ' . print_r($stmt->errorInfo(), true));
                throw new Exception('Database error');
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Set success message
            setFlashMessage('success', 'Your message has been sent successfully. We will get back to you soon.');
            
            // Email notification is optional and might be failing
            // Only attempt if we're sure the database part worked
            try {
                // Send email notification to admin
                $adminMessage = "
                    <p>New contact message received:</p>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Subject:</strong> {$subject}</p>
                    <p><strong>Message:</strong></p>
                    <p>{$message}</p>
                ";
                sendEmail(ADMIN_EMAIL, 'New Contact Message', $adminMessage);
            } catch (Exception $emailErr) {
                // Just log the email error but don't show to user since message is saved in DB
                error_log('Failed to send email notification: ' . $emailErr->getMessage());
            }
            
            $formSubmitted = true;
            
            // Redirect to prevent form resubmission
            redirect('contact');
            
        } catch (Exception $e) {
            // Rollback the transaction on error
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            
            error_log('Contact form error: ' . $e->getMessage());
            setFlashMessage('danger', 'There was an error sending your message. Please try again later.');
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
    }
}

// Include the contact view
require_once 'views/contact.php'; 