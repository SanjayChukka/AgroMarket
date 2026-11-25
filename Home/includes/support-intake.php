
<?php

require_once __DIR__ . '/html.php';


// Initialize the session if it is not already active.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Initialize the support CSRF token.
$_SESSION['support_token'] ??= bin2hex(random_bytes(32));


// Retrieve and clear the previous support notification.
$notice = $_SESSION['support_notice'] ?? '';

unset($_SESSION['support_notice']);

$error = '';


// Safely retrieve and trim submitted form values.
function supportField(string $name): string
{
    return is_string($_POST[$name] ?? null)
        ? trim($_POST[$name])
        : '';
}


// Identify the current user and portal role.
$portalRole = $_SESSION['user_role'] ?? '';

$owner = in_array($portalRole, ['buyer', 'farmer'], true)
    ? ($_SESSION['user_id'] ?? null)
    : null;


// Process support form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect contact information.
    $name = supportField('name');

    $email = supportField('email');

    $mobile = supportField('mobile');

    $role = $owner
        ? $portalRole
        : supportField('user_type');


    // Collect grievance or enquiry information.
    $category = supportField(
        $isGrievance ? 'grievance_type' : 'subject'
    );

    $description = supportField(
        $isGrievance ? 'description' : 'message'
    );

    $reference = supportField('reference');


    // Initialize attachment information.
    $file = $_FILES['attachment'] ?? [];

    $mime = $bytes = null;


    // Validate the CSRF token.
    if (
        !hash_equals(
            $_SESSION['support_token'],
            supportField('support_token')
        )
    ) {

        $error = 'Your form expired. Please submit it again.';

    } elseif (
        $name === '' ||
        strlen($name) > 120 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($email) > 254 ||

        !in_array(
            $role,
            ['farmer', 'buyer', 'other'],
            true
        ) ||

        !in_array(
            $category,
            [
                'account',
                'product',
                'technical',
                'order',
                'payment',
                'other'
            ],
            true
        ) ||

        $description === '' ||
        strlen($description) > 12000 ||
        strlen($mobile) > 24 ||
        strlen($reference) > 100
    ) {

        // Validate required fields and input lengths.
        $error = 'Enter your name, email, user type, category and a description of up to 12,000 bytes.';

    } elseif (
        ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
    ) {

        // Validate attachment upload status and file size.
        if (
            $file['error'] !== UPLOAD_ERR_OK ||
            $file['size'] > 2 * 1024 * 1024 ||
            !is_uploaded_file($file['tmp_name'])
        ) {

            $error = 'Choose a PDF, PNG or JPEG file up to 2 MB.';

        } else {

            // Determine the attachment MIME type.
            $mime = (new finfo(FILEINFO_MIME_TYPE))
                ->file($file['tmp_name']);


            // Allow only PDF, PNG and JPEG attachments.
            if (
                in_array(
                    $mime,
                    [
                        'application/pdf',
                        'image/png',
                        'image/jpeg'
                    ],
                    true
                )
            ) {

                // Read the attachment as binary data.
                $bytes = file_get_contents($file['tmp_name']);
            }


            // Ensure the attachment contains valid file data.
            if (!$bytes) {

                $error = 'Choose a valid PDF, PNG or JPEG attachment.';
            }
        }
    }


    // Save the support request if validation succeeds.
    if ($error === '') {

        // Establish the database connection.
        require_once __DIR__ . '/../config/db.php';


        // Generate a unique support ticket number.
        $number = 'AMC-' . strtoupper(
            bin2hex(random_bytes(8))
        );


        // Generate the subject based on the request type.
        $subject = ucfirst($category)
            . ($isGrievance ? ' complaint' : ' enquiry');


        try {

            // Prepare the support request insertion query.
            $stmt = $link->prepare(
                'INSERT INTO support_requests (
                    ticket_number,
                    user_id,
                    contact_name,
                    email,
                    mobile,
                    contact_role,
                    subject,
                    reference_text,
                    description,
                    file_type,
                    file_data
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )'
            );


            // Insert the support request into the database.
            $stmt->execute([
                $number,
                $owner,
                $name,
                $email,
                $mobile,
                $role,
                $subject,
                $reference,
                $description,
                $mime,
                $bytes
            ]);


            // Store the submission success notification.
            $_SESSION['support_notice'] =
                'Submitted successfully. Your reference is '
                . $number
                . '.';


            // Redirect to the corresponding support page.
            header(
                'Location: '
                . ($isGrievance ? 'grievance.php' : 'contact.php')
                . '#support-form',
                true,
                303
            );

            exit;

        } catch (mysqli_sql_exception $exception) {

            // Log unexpected database errors.
            error_log(
                'Support intake database error '
                . $exception->getCode()
            );


            // Display a user-friendly error message.
            $error = 'Your message could not be saved. Please try again.';
        }
    }
}
