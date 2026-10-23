
<?php
require_once "config.php";

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" ||
        $password === "" || $confirm_password === "") {
        $message = "Please fill in all fields.";
    } elseif (strlen($name) > 100) {
        $message = "Full name must be 100 characters or fewer.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
              strlen($email) > 255) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (:name, :email, :password)"
            );

            $stmt->execute([
                ":name" => $name,
                ":email" => $email,
                ":password" => password_hash(
                    $password,
                    PASSWORD_DEFAULT
                )
            ]);

            $success = true;
            $message = "Account created successfully!";

        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $message = "This email is already registered.";
            } else {
                error_log($e->getMessage());
                $message = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | MindBridge</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff, #f5f3ff);
        }

        .signup-box {
            width: 100%;
            max-width: 420px;
            padding: 35px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        }

        h1 {
            margin: 0 0 8px;
            text-align: center;
            color: #4338ca;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 28px;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        input:focus {
            outline: 2px solid #818cf8;
            border-color: #4f46e5;
        }

        button {
            width: 100%;
            margin-top: 24px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #4f46e5;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #3730a3;
        }

        .message {
            margin-top: 18px;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            overflow-wrap: anywhere;
            background: #f3f4f6;
            color: #374151;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #555;
        }

        a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="signup-box">
    <h1>MindBridge</h1>
    <p class="subtitle">Create your account</p>

    <form method="POST" action="signup.php">
        <label for="name">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            maxlength="100"
            autocomplete="name"
            required
        >

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="255"
            autocomplete="email"
            required
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <label for="confirm_password">Confirm Password</label>
        <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            minlength="8"
            autocomplete="new-password"
            required
        >

        <button type="submit">Create Account</button>
    </form>

    <?php if ($message !== ""): ?>
        <div class="message <?= $success ? 'success' : 'error' ?>"
             role="status">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <p class="login-link">
        Already have an account?
        <a href="login.php">Log in</a>
    </p>
</div>

<script>
const form = document.querySelector("form");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");

function checkPasswords() {
    if (confirmPassword.value === "") {
        confirmPassword.setCustomValidity("");
    } else if (password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords do not match.");
    } else {
        confirmPassword.setCustomValidity("");
    }
}

password.addEventListener("input", checkPasswords);
confirmPassword.addEventListener("input", checkPasswords);

form.addEventListener("submit", function (event) {
    checkPasswords();

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
    }
});
</script>

</body>
</html>
