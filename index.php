<?php
session_start();

$servername = 'localhost';
$username = "your-username";
$password = "your-password";
$dbname = "your-db-name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Serve manifest.json
if ($_SERVER['REQUEST_URI'] === '/manifest.json') {
    header('Content-Type: application/json');
    readfile('manifest.json');
    exit;
}

// Serve service worker
if ($_SERVER['REQUEST_URI'] === '/sw.js') {
    header('Content-Type: application/javascript');
    readfile('sw.js');
    exit;
}

// Login functionality
$users = [
    'admin' => ['password' => 'Adminpass', 'image' => 'https://yourdomain.com/pvt-info/admin.png'],
    'guest' => ['password' => 'Guestpass', 'image' => 'https://yourdomain.com/pvt-info/guest.png']
];

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (isset($users[$username]) && $users[$username]['password'] === $password) {
        $_SESSION['user'] = $username;
    } else {
        $login_error = "Invalid username or password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['query']) || isset($_POST['filterType'], $_POST['filterValue'])) {
    $search = isset($_POST['query']) ? $conn->real_escape_string($_POST['query']) : '';
    $filterType = isset($_POST['filterType']) ? $conn->real_escape_string($_POST['filterType']) : '';
    $filterValue = isset($_POST['filterValue']) ? $conn->real_escape_string($_POST['filterValue']) : '';

    // Query for the Master table with filter
    $sqlMaster = "SELECT * FROM Master WHERE 1=1";
    if ($search) {
        $sqlMaster .= " AND (FullName LIKE '%$search%' OR ID LIKE '%$search%' OR PermenantAddress LIKE '%$search%')";
    }
    if ($filterType && $filterValue) {
        if ($filterType === 'Atoll' || $filterType === 'Island') {
            $sqlMaster .= " AND $filterType LIKE '%$filterValue%'";
        }
    }
    $resultMaster = $conn->query($sqlMaster);

    // Query for the NUMBER table with filter
    $sqlNumber = "SELECT * FROM NUMBER WHERE 1=1";
    if ($search) {
        $sqlNumber .= " AND (NUMBER LIKE '%$search%' OR NAME LIKE '%$search%' OR OCUP LIKE '%$search%')";
    }
    if ($filterType && $filterValue) {
        if ($filterType === 'CITY' || $filterType === 'SEX' || $filterType === 'OCUP') {
            $sqlNumber .= " AND $filterType LIKE '%$filterValue%'";
        }
    }
    $resultNumber = $conn->query($sqlNumber);

    // Prepare results
    $masterResults = $numberResults = '';

    // Process Master table results
    if ($resultMaster->num_rows > 0) {
        while($row = $resultMaster->fetch_assoc()) {
            $copyText = "ID: " . $row["ID"] . "\n" .
                        "Name: " . $row["FullName"] . "\n" .
                        "DOB: " . $row["D.O.B"] . "\n" .
                        "Address: " . $row["PermenantAddress"] . "\n" .
                        "Atoll: " . $row["Atoll"] . "\n" .
                        "Island: " . $row["Island"];
            
            $masterResults .= "<div class='result-item'>" . 
                              "<button class='copy-btn' data-clipboard-text='" . htmlspecialchars($copyText, ENT_QUOTES) . "'>Copy</button>" .
                              "<strong>ID:</strong> " . htmlspecialchars($row["ID"]) . "<br>" .
                              "<strong>Name:</strong> " . htmlspecialchars($row["FullName"]) . "<br>" .
                              "<strong>DOB:</strong> " . htmlspecialchars($row["D.O.B"]) . "<br>" .
                              "<strong>Address:</strong> " . htmlspecialchars($row["PermenantAddress"]) . "<br>" .
                              "<strong>Atoll:</strong> " . htmlspecialchars($row["Atoll"]) . "<br>" .
                              "<strong>Island:</strong> " . htmlspecialchars($row["Island"]) . 
                              "</div>";
        }
    } else {
        $masterResults = "<p>No ID card details found</p>";
    }

    // Process NUMBER table results
    if ($resultNumber->num_rows > 0) {
        while($row = $resultNumber->fetch_assoc()) {
            $copyText = "Number: " . $row["NUMBER"] . "\n" .
                        "Name: " . $row["NAME"] . "\n" .
                        "City: " . $row["CITY"] . "\n" .
                        "Sex: " . $row["SEX"] . "\n" .
                        "Occupation: " . $row["OCUP"];
            
            $numberResults .= "<div class='result-item'>" . 
                              "<button class='copy-btn' data-clipboard-text='" . htmlspecialchars($copyText, ENT_QUOTES) . "'>Copy</button>" .
                              "<strong>Number:</strong> " . htmlspecialchars($row["NUMBER"]) . "<br>" .
                              "<strong>Name:</strong> " . htmlspecialchars($row["NAME"]) . "<br>" .
                              "<strong>City:</strong> " . htmlspecialchars($row["CITY"]) . "<br>" .
                              "<strong>Sex:</strong> " . htmlspecialchars($row["SEX"]) . "<br>" .
                              "<strong>Occupation:</strong> " . htmlspecialchars($row["OCUP"]) . 
                              "</div>";
        }
    } else {
        $numberResults = "<p>No phone numbers found</p>";
    }

    // Combine results
    echo json_encode([
        'master' => $masterResults,
        'number' => $numberResults
    ]);

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
     <!-- Set the title for the web app -->
    <title>ID AND NUMBERS</title>

    <!-- Set the home screen icon -->
    <link rel="apple-touch-icon" href="icon.png">

    <!-- Set the status bar appearance -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Enable web app mode (hides the browser UI) -->
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Set the app's name (displayed under the icon) -->
    <meta name="apple-mobile-web-app-title" content="MV INFO">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($_SESSION['user']) ? 'Search Database' : 'Login'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#3a86ff">
    <link rel="apple-touch-icon" href="/icon-192x192.png">
    <style>
        /* ... existing styles ... */
                :root {
            --bg-color: #1a1a1a;
            --text-color: #f0f0f0;
            --primary-color: #27e8a7;
            --secondary-color: #2a2a2a;
            --hover-color: #42675a;
        }
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
            .search-container {
        background-color: var(--secondary-color);
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        padding: 20px;
        margin-bottom: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .search-bar, .filter-select, .filter-input {
        flex: 1 1 200px;
        padding: 10px;
        border: 1px solid #444;
        border-radius: 6px;
        font-size: 16px;
        background-color: var(--bg-color);
        color: var(--text-color);
    }
    .search-button {
        background-color: var(--primary-color);
        color: var(--text-color);
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s;
        flex: 0 1 auto;
    }
    .search-button:hover {
        background-color: #2a76ef;
    }
        .results-container {
            display: flex;
            gap: 20px;
        }
        .results-column {
            flex: 1;
            background-color: var(--secondary-color);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            padding: 20px;
        }
        .result-item {
            background-color: var(--bg-color);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
        }
        h2 {
            color: var(--primary-color);
            margin-top: 0;
        }
        .copy-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .copy-btn:hover {
            background-color: #2a76ef;
        }
        .login-container {
            background-color: var(--secondary-color);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 300px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .login-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #444;
            border-radius: 6px;
            font-size: 16px;
            background-color: var(--bg-color);
            color: var(--text-color);
        }
        .login-button {
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }
        .login-button:hover {
            background-color: #2a76ef;
        }
        .error-message {
            color: #ff6b6b;
            margin-top: 10px;
        }
        .logout-link {
            color: var(--primary-color);
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }
        .user-select {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .user-option {
            text-align: center;
            cursor: pointer;
        }
        .user-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .password-container {
            display: none;
            margin-top: 20px;
        }
        .user-option.selected .user-image {
            border: 3px solid var(--primary-color);
        }
        @media (max-width: 768px) {
            .results-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="<?php echo !isset($_SESSION['user']) ? 'login-page' : ''; ?>">
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="login-container">
            <h2>Select User</h2>
            <div class="user-select">
                <?php foreach ($users as $username => $user): ?>
                    <div class="user-option" onclick="selectUser('<?php echo $username; ?>', this)">
                        <img src="<?php echo $user['image']; ?>" alt="<?php echo $username; ?>" class="user-image">
                        <div><?php echo ucfirst($username); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post" action="" id="login-form">
                <input type="hidden" name="username" id="selected-username">
                <div class="password-container" id="password-container">
                    <input type="password" name="password" placeholder="Password" required class="login-input">
                    <button type="submit" name="login" class="login-button">Login</button>
                </div>
            </form>
            <?php if (isset($login_error)): ?>
                <p class="error-message"><?php echo $login_error; ?></p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="container">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</h2>
            <div class="search-container">
                <input type="text" class="search-bar" id="search" placeholder="Search...">
                <select class="filter-select" id="filter-type">
                    <option value="">Select Filter</option>
                    <option value="Atoll">Atoll</option>
                    <option value="Island">Island</option>
                    <option value="CITY">City</option>
                    <option value="SEX">Sex</option>
                    <option value="OCUP">Occupation</option>
                </select>
                <input type="text" class="filter-input" id="filter-value" placeholder="Filter Value...">
                <button class="search-button" id="search-button">Search</button>
                <button class="clear-button" id="clear-button">Clear</button>
            </div>
            <div class="results-container">
                <div class="results-column">
                    <h2>ID Card Details</h2>
                    <div id="master-results"></div>
                </div>
                <div class="results-column">
                    <h2>Phone Numbers</h2>
                    <div id="number-results"></div>
                </div>
            </div>
            <a href="?logout=1" class="logout-link">Logout</a>
        </div>
    <?php endif; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <script>
    function selectUser(username, element) {
        console.log('User selected:', username);
        document.getElementById('selected-username').value = username;
        document.getElementById('password-container').style.display = 'block';
        
        // Remove 'selected' class from all user options
        var userOptions = document.getElementsByClassName('user-option');
        for (var i = 0; i < userOptions.length; i++) {
            userOptions[i].classList.remove('selected');
        }
        
        // Add 'selected' class to the clicked user option
        element.classList.add('selected');
    }

    $(document).ready(function() {
        function performSearch() {
            var query = $('#search').val();
            var filterType = $('#filter-type').val();
            var filterValue = $('#filter-value').val();
            $.ajax({
                url: "",
                method: "POST",
                data: {query: query, filterType: filterType, filterValue: filterValue},
                dataType: 'json',
                success: function(data) {
                    $('#master-results').html(data.master);
                    $('#number-results').html(data.number);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX error: " + textStatus + ' : ' + errorThrown);
                }
            });
        }

        $('#search-button').on('click', performSearch);

        $('#search, #filter-type, #filter-value').on('keypress', function(e) {
            if (e.which == 13) {
                performSearch();
            }
        });

        $('#clear-button').on('click', function() {
            $('#search').val('');
            $('#filter-type').val('');
            $('#filter-value').val('');
            $('#master-results').html('');
            $('#number-results').html('');
        });

        // Initialize clipboard.js
        new ClipboardJS('.copy-btn');

        // Add a success message when copying
        $(document).on('click', '.copy-btn', function() {
            var $this = $(this);
            $this.text('Copied!');
            setTimeout(function() {
                $this.text('Copy');
            }, 2000);
        });
    });

    // Add this at the end of your script
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then((reg) => console.log('Service worker registered.', reg))
                .catch((err) => console.log('Service worker registration failed:', err));
        });
    }
    </script>
</body>
</html>