<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "Weareone@2";
$dbname = "souk";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Insert project
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert_project'])) {
        $projectTitle = $_POST['project_title'];
        $category = $_POST['category'];
        $author = $_POST['author'];
        $yearPublished = intval($_POST['year_published']);
        $journalName = $_POST['journal_name'];
        $volume = intval($_POST['volume']);
        $issue = intval($_POST['issue']);
        $pageRange = $_POST['page_range'];
        $doi = $_POST['doi'];
        $qualityCertification = $_POST['quality_certification'];
        $ranking = intval($_POST['ranking']);
        $downloadLink = $_POST['download_link'];

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO project (ProjectTitle, Category, Author, YearPublished, JournalName, Volume, Issue, PageRange, DOI, QualityCertification, Ranking, DownloadLink) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt === false) {
            throw new Exception("MySQL prepare error: " . $conn->error);
        }

        // Bind and execute
        $stmt->bind_param('sssiissssssi', $projectTitle, $category, $author, $yearPublished, $journalName, $volume, $issue, $pageRange, $doi, $qualityCertification, $ranking, $downloadLink);
        if (!$stmt->execute()) {
            echo 'Execute error: ' . $stmt->error;
        } else {
            echo 'Project added successfully!';
        }
        $stmt->close();
    }
    
    // Delete project
    if (isset($_GET['delete'])) {
        $projectId = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM project WHERE ProjectID = ?");
        $stmt->bind_param('i', $projectId);

        if (!$stmt->execute()) {
            echo 'Execute error: ' . $stmt->error;
        } else {
            echo 'Project deleted successfully!';
        }
        $stmt->close();
    }

    // Update project
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_project'])) {
        $projectId = intval($_POST['project_id']);
        $projectTitle = $_POST['project_title'];
        $category = $_POST['category'];
        $author = $_POST['author'];
        $yearPublished = intval($_POST['year_published']);
        $journalName = $_POST['journal_name'];
        $volume = intval($_POST['volume']);
        $issue = intval($_POST['issue']);
        $pageRange = $_POST['page_range'];
        $doi = $_POST['doi'];
        $qualityCertification = $_POST['quality_certification'];
        $ranking = intval($_POST['ranking']);
        $downloadLink = $_POST['download_link'];

        $stmt = $conn->prepare("UPDATE project SET ProjectTitle = ?, Category = ?, Author = ?, YearPublished = ?, JournalName = ?, Volume = ?, Issue = ?, PageRange = ?, DOI = ?, QualityCertification = ?, Ranking = ?, DownloadLink = ? WHERE ProjectID = ?");
        $stmt->bind_param('sssiissssssii', $projectTitle, $category, $author, $yearPublished, $journalName, $volume, $issue, $pageRange, $doi, $qualityCertification, $ranking, $downloadLink, $projectId);
        
        if (!$stmt->execute()) {
            echo 'Execute error: ' . $stmt->error;
        } else {
            echo 'Project updated successfully!';
        }
        $stmt->close();
    }
    
    // Import CSV data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $csvFile = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($csvFile, 'r');

            while (($data = fgetcsv($handle)) !== FALSE) {
                $projectTitle = $data[0];
                $category = $data[1];
                $author = $data[2];
                $yearPublished = intval($data[3]);
                $journalName = $data[4];
                $volume = intval($data[5]);
                $issue = intval($data[6]);
                $pageRange = $data[7];
                $doi = $data[8];
                $qualityCertification = $data[9];
                $ranking = intval($data[10]);
                $downloadLink = $data[11];

                $stmt = $conn->prepare("INSERT INTO project (ProjectTitle, Category, Author, YearPublished, JournalName, Volume, Issue, PageRange, DOI, QualityCertification, Ranking, DownloadLink) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt === false) {
                    throw new Exception("MySQL prepare error: " . $conn->error);
                }
                
                $stmt->bind_param('sssiissssssi', $projectTitle, $category, $author, $yearPublished, $journalName, $volume, $issue, $pageRange, $doi, $qualityCertification, $ranking, $downloadLink);
                
                if (!$stmt->execute()) {
                    echo ($stmt->error);
                }
                $stmt->close();
            }
            fclose($handle);
            echo 'Data imported successfully!';
        } else {
            echo 'Failed to upload file.';
        }
    }

    // Fetch all projects
    $result = $conn->query("SELECT * FROM project");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <!-- CSV Import Form -->
    <h2>นำเข้าข้อมูลจาก CSV</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <input type="submit" name="import_csv" value="นำเข้าข้อมูล">
    </form>
    <!-- Insert Project Form -->
    <h2>เพิ่มโครงการใหม่</h2>
    <form method="post">
        ชื่อโครงการ: <input type="text" name="project_title" required><br>
        หมวดหมู่: <input type="text" name="category" required><br>
        ชื่อผู้แต่ง: <input type="text" name="author" required><br>
        ปีที่เผยแพร่: <input type="number" name="year_published" required><br>
        ชื่อวารสาร: <input type="text" name="journal_name" required><br>
        เล่มที่ (Volume): <input type="number" name="volume"><br>
        ฉบับที่ (Issue): <input type="number" name="issue"><br>
        หน้าช่วง (Page Range): <input type="text" name="page_range"><br>
        DOI: <input type="text" name="doi"><br>
        การรับรองคุณภาพ: <input type="text" name="quality_certification"><br>
        อันดับ (Ranking): <input type="number" name="ranking"><br>
        ลิงก์ดาวน์โหลด: <input type="text" name="download_link"><br>
        <input type="submit" name="insert_project" value="เพิ่มโครงการ">
    </form>

    <!-- Display Projects -->
    <h2>รายการโครงการ</h2>
    <table border="1">
        <tr>
            <th>ชื่อโครงการ</th>
            <th>หมวดหมู่</th>
            <th>ชื่อผู้แต่ง</th>
            <th>ปีที่เผยแพร่</th>
            <th>ชื่อวารสาร</th>
            <th>เล่มที่</th>
            <th>ฉบับที่</th>
            <th>หน้าช่วง</th>
            <th>DOI</th>
            <th>การรับรองคุณภาพ</th>
            <th>อันดับ</th>
            <th>ลิงก์ดาวน์โหลด</th>
            <th>จัดการ</th>
        </tr>
        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ProjectTitle']); ?></td>
                    <td><?php echo htmlspecialchars($row['Category']); ?></td>
                    <td><?php echo htmlspecialchars($row['Author']); ?></td>
                    <td><?php echo htmlspecialchars($row['YearPublished']); ?></td>
                    <td><?php echo htmlspecialchars($row['JournalName']); ?></td>
                    <td><?php echo htmlspecialchars($row['Volume']); ?></td>
                    <td><?php echo htmlspecialchars($row['Issue']); ?></td>
                    <td><?php echo htmlspecialchars($row['PageRange']); ?></td>
                    <td><?php echo htmlspecialchars($row['DOI']); ?></td>
                    <td><?php echo htmlspecialchars($row['QualityCertification']); ?></td>
                    <td><?php echo htmlspecialchars($row['Ranking']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($row['DownloadLink']); ?>">ดาวน์โหลด</a></td>
                    <td>
                        <a href="?delete=<?php echo $row['ProjectID']; ?>" onclick="return confirm('คุณแน่ใจว่าต้องการลบโครงการนี้หรือไม่?');">ลบ</a>
                        <a href="?edit=<?php echo $row['ProjectID']; ?>">แก้ไข</a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="13">ไม่มีข้อมูลโครงการ</td></tr>
        <?php } ?>
    </table>

    <?php
    // Show edit form if requested
    if (isset($_GET['edit'])) {
        $projectId = intval($_GET['edit']);
        $stmt = $conn->prepare("SELECT * FROM project WHERE ProjectID = ?");
        $stmt->bind_param('i', $projectId);
        $stmt->execute();
        $result = $stmt->get_result();
        $project = $result->fetch_assoc();
        $stmt->close();
    ?>
        <h2>แก้ไขโครงการ</h2>
        <form method="post">
            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['ProjectID']); ?>">
            ชื่อโครงการ: <input type="text" name="project_title" value="<?php echo htmlspecialchars($project['ProjectTitle']); ?>" required><br>
            หมวดหมู่: <input type="text" name="category" value="<?php echo htmlspecialchars($project['Category']); ?>" required><br>
            ชื่อผู้แต่ง: <input type="text" name="author" value="<?php echo htmlspecialchars($project['Author']); ?>" required><br>
            ปีที่เผยแพร่: <input type="number" name="year_published" value="<?php echo htmlspecialchars($project['YearPublished']); ?>" required><br>
            ชื่อวารสาร: <input type="text" name="journal_name" value="<?php echo htmlspecialchars($project['JournalName']); ?>" required><br>
            เล่มที่ (Volume): <input type="number" name="volume" value="<?php echo htmlspecialchars($project['Volume']); ?>"><br>
            ฉบับที่ (Issue): <input type="number" name="issue" value="<?php echo htmlspecialchars($project['Issue']); ?>"><br>
            หน้าช่วง (Page Range): <input type="text" name="page_range" value="<?php echo htmlspecialchars($project['PageRange']); ?>"><br>
            DOI: <input type="text" name="doi" value="<?php echo htmlspecialchars($project['DOI']); ?>"><br>
            การรับรองคุณภาพ: <input type="text" name="quality_certification" value="<?php echo htmlspecialchars($project['QualityCertification']); ?>"><br>
            อันดับ (Ranking): <input type="number" name="ranking" value="<?php echo htmlspecialchars($project['Ranking']); ?>"><br>
            ลิงก์ดาวน์โหลด: <input type="text" name="download_link" value="<?php echo htmlspecialchars($project['DownloadLink']); ?>"><br>
            <input type="submit" name="update_project" value="แก้ไขโครงการ">
        </form>
    <?php } ?>

    <?php $conn->close(); ?>
</body>
</html>
