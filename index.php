<!-- mao nig code para kita ag mga data sa database -->
<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "calibration";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination variables
$results_per_page = 10; // Number of results to display per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Current page number

// Calculate the starting record
$start_from = ($page - 1) * $results_per_page;

// SQL query to fetch paginated results
$sql = "SELECT * FROM weight_data WHERE name IN ('small', 'medium', 'large', 'xl') LIMIT $start_from, $results_per_page";
$result = $conn->query($sql);

// SQL query to count total records
$count_sql = "SELECT COUNT(*) AS total FROM weight_data WHERE name IN ('small', 'medium', 'large', 'xl')";
$count_result = $conn->query($count_sql);
$row = $count_result->fetch_assoc();
$total_records = $row['total'];
$total_pages = ceil($total_records / $results_per_page);

// Calculate the start and end page numbers for pagination
$pages_to_show = 5;
$start_page = max(1, $page - floor($pages_to_show / 2));
$end_page = min($total_pages, $start_page + $pages_to_show - 1);

// Adjust the start page if end page is not enough
if ($end_page - $start_page + 1 < $pages_to_show) {
    $start_page = max(1, $end_page - $pages_to_show + 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weight Data Display</title>
    <style>
        /* Basic reset */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* Flexbox centering */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .container {
            width: 80%;
            max-width: 800px; /* Maximum width of the container */
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align text inside the container */
        }

        h1 {
            color: #333;
            margin-bottom: 20px; /* Space below the header */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto; /* Center table horizontally */
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            text-decoration: none;
            color: #333;
            padding: 8px 16px;
            border: 1px solid #ddd;
            margin: 0 4px;
            border-radius: 4px;
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }

        .pagination a:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Weight Data</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Size</th>
                    <th>Weight</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['weight']); ?></td>
                            <td><?php echo htmlspecialchars($row['timestamp']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php
            // Previous page link
            if ($page > 1) {
                echo '<a href="?page=' . ($page - 1) . '">Previous</a>';
            }

            // Page number links
            for ($i = $start_page; $i <= $end_page; $i++) {
                $class = ($i == $page) ? 'active' : '';
                echo '<a href="?page=' . $i . '" class="' . $class . '">' . $i . '</a>';
            }

            // Next page link
            if ($page < $total_pages) {
                echo '<a href="?page=' . ($page + 1) . '">Next</a>';
            }
            ?>
        </div>
    </div>

    <script>
        // JavaScript for auto-update
        function fetchData() {
            const page = <?php echo $page; ?>;
            fetch(`fetch_data.php?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('data-table-body');
                    tableBody.innerHTML = ''; // Clear current content

                    data.data.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${row.id}</td>
                            <td>${row.size}</td>
                            <td>${row.weight}</td>
                            <td>${row.timestamp}</td>
                        `;
                        tableBody.appendChild(tr);
                    });

                    // Update pagination if necessary
                    const pagination = document.querySelector('.pagination');
                    pagination.innerHTML = ''; // Clear current content

                    // Previous page link
                    if (page > 1) {
                        const prevLink = document.createElement('a');
                        prevLink.href = `?page=${page - 1}`;
                        prevLink.textContent = 'Previous';
                        pagination.appendChild(prevLink);
                    }

                    // Page number links
                    for (let i = <?php echo $start_page; ?>; i <= <?php echo $end_page; ?>; i++) {
                        const pageLink = document.createElement('a');
                        pageLink.href = `?page=${i}`;
                        pageLink.textContent = i;
                        if (i === page) pageLink.classList.add('active');
                        pagination.appendChild(pageLink);
                    }

                    // Next page link
                    if (page < <?php echo $total_pages; ?>) {
                        const nextLink = document.createElement('a');
                        nextLink.href = `?page=${page + 1}`;
                        nextLink.textContent = 'Next';
                        pagination.appendChild(nextLink);
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Fetch data every 5 seconds
        setInterval(fetchData, 5000);
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
