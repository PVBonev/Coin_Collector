<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, email, profile_image, bio, location, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User not found.");

$avatarUrl = '';
$hasAvatar = false;
if (!empty($user['profile_image'])) {
    if (file_exists($user['profile_image'])) {
        $avatarUrl = $user['profile_image'];
        $hasAvatar = true;
    }
}
$initial = strtoupper(substr($user['username'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Settings - Coin Collector</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="settings-container">

            <div class="settings-header">
                <h1 style="margin: 0;">Account Settings</h1>
                <button type="button" id="editProfileBtn" class="btn" style="background: #6c757d; color: white;">
                    &#9998; Edit Profile
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form id="settingsForm" action="actions/update_settings.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <input type="hidden" name="current_password" id="hidden_current_password">

                <fieldset id="profileFields" disabled style="border: none; padding: 0; margin: 0;">

                    <div class="profile-preview">
                        <?php if ($hasAvatar): ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" class="avatar-large">
                        <?php else: ?>
                            <div class="avatar-placeholder"><?php echo $initial; ?></div>
                        <?php endif; ?>

                        <div class="user-meta">
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                            <p>Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?> </p>

                            <div class="edit-only" style="margin-top: 10px;">
                                <label style="font-size: 0.85rem; font-weight: bold; display: block; margin-bottom: 5px;">Change Photo:</label>
                                <input type="file" name="profile_pic" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="emailInput" class="form-control"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control"
                            value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="Not set">
                    </div>

                    <div class="form-group">
                        <label>Bio</label>
                        <textarea name="bio" class="form-control" placeholder="Not set"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <div class="edit-only">
                        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

                        <h3 style="margin-top: 0; color: #555;">Change Password</h3>
                        <p style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">Leave blank if you don't want to change it.</p>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" id="newPassInput" class="form-control" placeholder="New password">
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                        </div>
                    </div>

                </fieldset>

                <div id="actionButtons" class="edit-only" style="margin-top: 20px; gap: 10px; display: none;">
                    <button type="button" onclick="cancelEdit()" class="btn" style="background: #ccc; color: #333; flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-accent" style="flex: 2;">Save Changes</button>
                </div>
            </form>

            <div class="danger-zone">
                <button onclick="openDeleteModal()" class="btn-delete">Delete Account</button>
            </div>
        </div>
    </div>

    <div id="verifyModal" class="modal-overlay">
        <div class="modal-box">
            <h2 style="margin-top: 0; color: #333;">Security Check</h2>
            <p>You are changing sensitive information.</p>
            <p>Please enter your <strong>Current Password</strong> to confirm.</p>
            <input type="password" id="verify_pass_input" class="form-control" style="margin-bottom: 15px;" placeholder="Current Password">
            <div class="modal-buttons">
                <button onclick="closeVerifyModal()" class="btn" style="background: #ccc; color: #333;">Cancel</button>
                <button onclick="confirmVerify()" class="btn btn-accent">Confirm & Save</button>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <h2 style="margin-top: 0; color: #dc3545;">Delete Account?</h2>
            <p>Are you sure? This will delete all your coins and data.</p>
            <div class="modal-buttons">
                <button onclick="closeDeleteModal()" class="btn" style="background: #ccc; color: #333;">Cancel</button>
                <form action="actions/update_settings.php" method="POST">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="submit" class="btn" style="background: #dc3545; color: white;">Yes, Delete It</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const initialEmail = "<?php echo $user['email']; ?>";
        const form = document.getElementById('settingsForm');
        const verifyModal = document.getElementById('verifyModal');
        const deleteModal = document.getElementById('deleteModal');
        const verifyInput = document.getElementById('verify_pass_input');
        const hiddenPass = document.getElementById('hidden_current_password');

        const fieldset = document.getElementById('profileFields');
        const editBtn = document.getElementById('editProfileBtn');
        const actionButtons = document.getElementById('actionButtons');
        const editOnlyElements = document.querySelectorAll('.edit-only');

        editBtn.addEventListener('click', function() {
            fieldset.disabled = false;
            editBtn.style.display = 'none';

            actionButtons.style.display = 'flex';
            editOnlyElements.forEach(el => el.style.display = 'block');
        });

        function cancelEdit() {
            location.reload();
        }

        form.addEventListener('submit', function(e) {
            const newEmail = document.getElementById('emailInput').value;
            const newPass = document.getElementById('newPassInput').value;

            if ((newEmail !== initialEmail || newPass !== "") && hiddenPass.value === "") {
                e.preventDefault();
                verifyModal.style.display = 'flex';
                verifyInput.focus();
            }
        });

        function confirmVerify() {
            if (verifyInput.value === "") {
                alert("Please enter your current password.");
                return;
            }
            hiddenPass.value = verifyInput.value;
            form.submit();
        }

        function closeVerifyModal() {
            verifyModal.style.display = 'none';
            verifyInput.value = "";
        }

        function openDeleteModal() {
            deleteModal.style.display = 'flex';
        }

        function closeDeleteModal() {
            deleteModal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == verifyModal) closeVerifyModal();
            if (event.target == deleteModal) closeDeleteModal();
        }
    </script>
</body>

</html>