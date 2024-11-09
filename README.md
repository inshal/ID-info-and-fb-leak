# ID-info-and-fb-leak

<h1>Project Setup Guide</h1>

<h2>Introduction</h2>
<p>This guide will help you set up the project on your local machine. you can do simple google search and find the database of facebook data leak and scap your own id info from maldivies elections website</p>
<p>And this php can act as a PWA so can be installed as an app to IOS</p>

<h2>Prerequisites</h2>
<ul>
    <li>PHP 7.0 or higher</li>
    <li>MySQL database</li>
    <li>Web server (e.g., Apache, Nginx)</li>
</ul>

<h2>Installation Steps</h2>
<ol>
    <li>
        <h3>Clone the Repository</h3>
        <p>Use the following command to clone the repository:</p>
        <pre><code>git clone https://github.com/inshal/ID-info-and-fb-leak.git</code></pre>
    </li>
    <li>
        <h3>Navigate to the Project Directory</h3>
        <pre><code>cd ID-info-and-fb-leak</code></pre>
    </li>
    <li>
        <h3>Set Up the Database</h3>
        <p>Create a new database in MySQL and import the provided SQL file:</p>
        <pre><code>mysql -u your-username -p your-db-name < database.sql</code></pre>
    </li>
    <li>
        <h3>Configure Database Credentials</h3>
        <p>Edit the <code>index.php</code> file to set your database credentials:</p>
        <pre><code>
$servername = 'localhost';
$username = 'your-username';
$password = 'your-password';
$dbname = 'your-db-name';
        </code></pre>
    </li>
    <li>
        <h3>Start the Web Server</h3>
        <p>Make sure your web server is running and navigate to <code>http://localhost/ID-info-and-fb-leak</code> in your browser.</p>
    </li>
</ol>

<h2>Usage</h2>
<p>Follow the on-screen instructions to log in and use the application.</p>

<h2>Contributing</h2>
<p>Feel free to submit issues or pull requests to improve the project.</p>

<h2>License</h2>
<p>This project is licensed under the MIT License.</p>
