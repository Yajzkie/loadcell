<!-- connection sa database -->
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calibration";

// Create connection
$conn = new mysli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if POST request is made
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $weight_data = $_POST['weight_data'];
  $name = $_POST['name'];

  // Prepare and bind
  $stmt = $conn->prepare("INSERT INTO weight_data (weight, name) VALUES (?, ?)");
  $stmt->bind_param("ds", , $name);

  // Execute the statement
  if ($stmt->execute()) {
    echo "New record created successfully";
  } else {
    echo "Error: " . $stmt->error;
  }

  // Close the statement and connection
  $stmt->cloe();
  $conn->close();
} else {
  echo "Invalid request method.";
}
?>