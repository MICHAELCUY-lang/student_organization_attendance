// modules/documents/process.php
<?php
require_once '../../config/config.php';
require_once '../../includes/auth.php';

// Check if action is provided
if (!isset($_POST['action']) && !isset($_GET['action'])) {
    setFlashMessage('document_message', 'Invalid request', 'danger');
    redirect(BASE_URL . '/modules/documents/list.php');
}

// Determine the action
$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

switch ($action) {
    case 'upload':
        uploadDocument();
        break;
    case 'delete':
        deleteDocument();
        break;
    default:
        setFlashMessage('document_message', 'Invalid action', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
}

/**
 * Upload new document
 */
function uploadDocument() {
    global $conn;
    
    // Check required fields
    if (empty($_POST['meeting_id']) || empty($_POST['nama_file']) || empty($_FILES['document_file']['name'])) {
        setFlashMessage('document_message', 'Please fill all required fields and select a file', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Sanitize and validate input
    $meetingId = (int) $_POST['meeting_id'];
    $namaFile = sanitize($_POST['nama_file']);
    
    // Check if meeting exists
    $checkQuery = "SELECT meeting_id FROM meetings WHERE meeting_id = $meetingId";
    $checkResult = $conn->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        setFlashMessage('document_message', 'Meeting not found', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Validate file
    $file = $_FILES['document_file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Get file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file extensions
    $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
    
    // Check if extension is allowed
    if (!in_array($fileExt, $allowedExts)) {
        setFlashMessage('document_message', 'Invalid file type. Allowed types: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, TXT', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Check file size (5MB max)
    if ($fileSize > 5 * 1024 * 1024) {
        setFlashMessage('document_message', 'File is too large. Maximum size is 5MB', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Check for upload errors
    if ($fileError !== 0) {
        setFlashMessage('document_message', 'Error uploading file. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
    
    // Create unique filename to prevent overwriting
    $newFileName = uniqid('doc_') . '.' . $fileExt;
    $uploadPath = UPLOAD_PATH . $newFileName;
    
    // Create uploads directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        // Insert document record
        $query = "
            INSERT INTO documents (meeting_id, nama_file, path_file)
            VALUES ($meetingId, '$namaFile', '$newFileName')
        ";
        
        if ($conn->query($query)) {
            setFlashMessage('document_message', 'Document uploaded successfully', 'success');
            redirect(BASE_URL . '/modules/documents/list.php');
        } else {
            // Delete uploaded file if database insertion fails
            unlink($uploadPath);
            setFlashMessage('document_message', 'Failed to save document record: ' . $conn->error, 'danger');
            redirect(BASE_URL . '/modules/documents/upload.php');
        }
    } else {
        setFlashMessage('document_message', 'Failed to upload file. Please try again.', 'danger');
        redirect(BASE_URL . '/modules/documents/upload.php');
    }
}

/**
 * Delete document
 */
function deleteDocument() {
    global $conn;
    
    // Check if ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        setFlashMessage('document_message', 'Document ID is required', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
    }
    
    $documentId = (int) $_GET['id'];
    
    // Get document info
    $query = "SELECT * FROM documents WHERE document_id = $documentId";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        setFlashMessage('document_message', 'Document not found', 'danger');
        redirect(BASE_URL . '/modules/documents/list.php');
    }
    
    $document = $result->fetch_assoc();
    $filePath = UPLOAD_PATH . $document['path_file'];
    
    // Delete file if it exists
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    // Delete document record
    $deleteQuery = "DELETE FROM documents WHERE document_id = $documentId";
    
    if ($conn->query($deleteQuery)) {
        setFlashMessage('document_message', 'Document deleted successfully', 'success');
    } else {
        setFlashMessage('document_message', 'Failed to delete document: ' . $conn->error, 'danger');
    }
    
    redirect(BASE_URL . '/modules/documents/list.php');
}