
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Registration | AgroMarket Connect</title>
    <link rel="stylesheet" href="./style/register.css">
</head>
<body>

<main class="registration-page">

    <section class="registration-container">

        <div class="registration-header">
            <div class="logo">AC</div>
            <h2>Farmer Registration</h2>
            <p>Create your farmer account to start selling crops.</p>
        </div>

        <form action="" method="POST" class="registration-form">

            <h3>Personal Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile"
                           pattern="[0-9]{10}" maxlength="10" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password"
                           name="confirm_password" required>
                </div>
            </div>

            <h3>Farm Information</h3>

            <div class="form-group">
                <label for="farm_name">Farm Name (Optional)</label>
                <input type="text" id="farm_name" name="farm_name">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="state">State</label>
                    <select id="state" name="state" required>
                        <option value="">Select State</option>
                        <option value="Telangana">Telangana</option>
                        <option value="Andhra Pradesh">Andhra Pradesh</option>
                        <option value="Karnataka">Karnataka</option>
                        <option value="Maharashtra">Maharashtra</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="district">District</label>
                    <input type="text" id="district" name="district" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="village">Village</label>
                    <input type="text" id="village" name="village" required>
                </div>

                <div class="form-group">
                    <label for="land_size">Land Size (Acres)</label>
                    <input type="number" id="land_size" name="land_size"
                           min="0" step="0.01">
                </div>
            </div>

            <button type="submit" name="register">
                Create Farmer Account
            </button>

        </form>

        <div class="registration-footer">
            <p>Already registered? <a href="login.php">Login</a></p>
            <a href="register.php">Back to Account Selection</a>
        </div>

    </section>

</main>

</body>
</html>
