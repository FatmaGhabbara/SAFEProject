<?php
session_start();
require_once '../../../config.php';
require_once '../../../model/SupportRequest.php';
require_once '../../../model/User.php';
require_once '../../../controller/helpers.php';

// Check if logged in
if (!isLoggedIn()) {
    header('Location: ../../frontoffice/login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Get user's requests
$requests = findSupportRequestsByUser($userId);

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Requests - SAFEProject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #2e3141;
            background-image: linear-gradient(to top, rgba(46, 49, 65, 0.8), rgba(46, 49, 65, 0.8)), url("../images/bg.jpg");
            background-size: auto, cover;
            background-attachment: fixed;
            background-position: center;
            min-height: 100vh;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .request-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .request-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .request-card h5 {
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        .request-description {
            word-wrap: break-word;
            overflow-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
            max-width: 100%;
            overflow: hidden;
        }
        .badge-custom {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
        }
        .badge-en_attente { background: #ffc107; color: #000; }
        .badge-assignee { background: #17a2b8; color: #fff; }
        .badge-en_cours { background: #007bff; color: #fff; }
        .badge-terminee { background: #28a745; color: #fff; }
        .badge-annulee { background: #6c757d; color: #fff; }
        .badge-basse { background: #e9ecef; color: #495057; }
        .badge-moyenne { background: #ffc107; color: #000; }
        .badge-haute { background: #dc3545; color: #fff; }
        .btn-view {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            transition: all 0.3s;
        }
        .btn-view:hover {
            background: #5568d3;
            color: white;
            transform: scale(1.05);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<?php include '../../includes/navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-inbox"></i> My Support Requests</h1>
        <p class="mb-0">View and manage your psychological support requests</p>
    </div>
</div>

<div class="container mb-5">
    <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $flash['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-6">
            <h3>All Requests (<?php echo count($requests); ?>)</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="support_form.php" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> New Request
            </a>
        </div>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No Requests Yet</h3>
            <p class="text-muted">You haven't created any support requests yet.</p>
            <a href="support_form.php" class="btn btn-primary btn-lg">
                <i class="fas fa-plus-circle"></i> Create Your First Request
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $requestObj): ?>
                <?php
                // $requestObj is already a SupportRequest object
                $user = $requestObj->getUser();
                $counselorId = $requestObj->getCounselorId();
                $counselorUser = $counselorId ? getCounselorById($counselorId) : null;
                $messages = findMessagesByRequest($requestObj->getId());
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="request-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="mb-0 flex-grow-1 me-2" style="color: #333; word-wrap: break-word; overflow-wrap: break-word;">
                                <?php echo htmlspecialchars($requestObj->getTitre()); ?>
                            </h5>
                            <span class="badge badge-custom badge-<?php echo $requestObj->getStatut() ?? 'en_attente'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $requestObj->getStatut() ?? 'En attente')); ?>
                            </span>
                        </div>

                        <p class="text-muted small mb-3 request-description" style="color: #666 !important;">
                            <?php echo htmlspecialchars(substr($requestObj->getDescription(), 0, 100)); ?>...
                        </p>

                        <div class="mb-3">
                            <span class="badge badge-custom badge-<?php echo $requestObj->getUrgence(); ?>">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo ucfirst($requestObj->getUrgence()); ?>
                            </span>
                        </div>

                        <div class="mb-3" style="color: #666;">
                            <small>
                                <i class="fas fa-calendar"></i>
                                <?php echo date('d/m/Y H:i', strtotime($requestObj->getDateCreation())); ?>
                            </small>
                        </div>

                        <?php if ($counselorUser): ?>
                            <div class="mb-3" style="color: #667eea;">
                                <small>
                                    <i class="fas fa-user-md"></i>
                                    <strong>Counselor:</strong> <?php echo $counselorUser->getFullName(); ?>
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="mb-3" style="color: #999;">
                                <small>
                                    <i class="fas fa-clock"></i>
                                    Waiting for counselor assignment
                                </small>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3" style="color: #666;">
                            <small>
                                <i class="fas fa-comments"></i>
                                <?php echo count($messages); ?> message(s)
                            </small>
                        </div>

                        <div class="text-end">
                            <a href="request_details.php?id=<?php echo $requestObj->getId(); ?>" class="btn btn-view me-2">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <!-- L'utilisateur peut toujours supprimer sa demande -->
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteRequestModal<?php echo $requestObj->getId(); ?>"
                                    title="Supprimer la demande">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Générer les modals de confirmation de suppression pour chaque demande
foreach ($requests as $requestObj):
?>
<!-- Modal de confirmation de suppression pour la demande <?php echo $requestObj->getId(); ?> -->
<div class="modal fade" id="deleteRequestModal<?php echo $requestObj->getId(); ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer définitivement cette demande ?</p>
                <p class="text-muted small mb-0">
                    <strong><?php echo htmlspecialchars($requestObj->getTitre()); ?></strong><br>
                    <strong>Cette action est irréversible.</strong><br>
                    Tous les messages associés seront également supprimés.<br>
                    <?php if ($requestObj->getCounselorId()): ?>
                    <span class="text-danger">La conversation avec le conseiller sera également supprimée de son côté.</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Annuler
                </button>
                <form action="../../../controller/support/user_delete_request.php" method="POST" class="d-inline">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="request_id" value="<?php echo $requestObj->getId(); ?>">
                    <button type="submit" class="btn btn-danger">
                        Oui, supprimer
                    </button>
                </form>
            </div>
            </div>
        </div>
    </div>
<?php
endforeach;
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
