<?php

$pageTitle = "Admin login";

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to database
require_once __DIR__ . "/../config/database.php";

// Initialize variables
$errors = [];
$email = "";

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verify CSRF token
    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    // Collect form data
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Validate inputs
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password === "") {
        $errors[] = "Please enter your password.";
    }

    // Authenticate the account before choosing its dashboard.
    if (empty($errors)) {

        try {

            $query = "SELECT id, full_name, email, password,
                             student_number, department, role
                      FROM users
                      WHERE email = :email
                      AND role = 'admin'
                      LIMIT 1";

            $stmt = $conn->prepare($query);

            $stmt->execute([
                ":email" => $email
            ]);

            $user = $stmt->fetch();

            // Verify password
            if ($user && password_verify($password, $user["password"])) {

                // Prevent session fixation
                session_regenerate_id(true);

                // Store user information in session
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["full_name"] = $user["full_name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["student_number"] = $user["student_number"];
                $_SESSION["department"] = $user["department"];
                $_SESSION["role"] = $user["role"];

                // Rotate CSRF token
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

                header("Location: ../admin/dashboard.php");
                exit();

            } else {

                $errors[] = "Incorrect email or password.";

            }

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $errors[] = "Something went wrong. Please try again later.";
        }
    }
}

// Load website header

include __DIR__ . "/../includes/header.php";

?>

<section class="form-container">

    <h1><?= htmlspecialchars($pageTitle) ?></h1>

    <!-- Display errors -->
    <?php if (!empty($errors)): ?>

        <div class="message error-message">

            <?php foreach ($errors as $error): ?>

                <p><?= htmlspecialchars($error) ?></p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" action="">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars($_SESSION["csrf_token"]) ?>"
        >

        <div class="form-group">

            <label for="email">University Email</label>

            <input
                type="email"
                id="email"
                name="email"
                value="<?= htmlspecialchars($email) ?>"
                placeholder="Enter your email"
                required
            >

        </div>

        <div class="form-group">

            <label for="password">Password</label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                required
            >

        </div>

        <button type="submit" class="btn btn-primary">
            Login
        </button>

    </form>

</section>

<?php include __DIR__ . "/../includes/footer.php"; ?>
