<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmtIncoming = $pdo->prepare("
    SELECT t.*, u.username as partner_name, u.profile_image
    FROM trades t
    JOIN users u ON t.sender_id = u.id
    WHERE t.receiver_id = ? 
    ORDER BY CASE WHEN t.status = 'pending' THEN 1 ELSE 2 END, t.created_at DESC
");
$stmtIncoming->execute([$user_id]);
$incoming_trades = $stmtIncoming->fetchAll();

$stmtSent = $pdo->prepare("
    SELECT t.*, u.username as partner_name, u.profile_image
    FROM trades t
    JOIN users u ON t.receiver_id = u.id
    WHERE t.sender_id = ?
    ORDER BY CASE WHEN t.status = 'pending' THEN 1 ELSE 2 END, t.created_at DESC
");
$stmtSent->execute([$user_id]);
$sent_trades = $stmtSent->fetchAll();

$count_incoming = count($incoming_trades);
$count_sent = count($sent_trades);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trade Center</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .tabs-header {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            color: #666;
            font-weight: bold;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            color: var(--accent-color);
            background: #f9f9f9;
        }

        .tab-btn.active {
            color: var(--accent-color);
            border-bottom-color: var(--accent-color);
        }

        .tab-content {
            display: none; 
            animation: fadeIn 0.3s ease-in-out;
        }

        .tab-content.active {
            display: block; 
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .trade-card {
            background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;
            border-left: 5px solid #ccc; transition: transform 0.2s;
        }
        .trade-card:hover { transform: translateX(5px); }
        
        .status-pending { border-left-color: var(--accent-color); background: #fffdf5; }
        .status-accepted { border-left-color: #28a745; }
        .status-completed { border-left-color: #155724; background: #f0fff4; }
        .status-declined { border-left-color: #dc3545; opacity: 0.7; }
        .status-cancelled { border-left-color: #6c757d; opacity: 0.7; }

        .partner-info { display: flex; align-items: center; gap: 15px; }
        
        .partner-avatar { 
            width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd; 
        }
        
        .partner-initial {
            width: 50px; height: 50px; border-radius: 50%; 
            background: #0056b3; color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem;
            text-transform: uppercase;
            border: 2px solid #eef;
        }
        
        .trade-status-badge {
            padding: 5px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; text-transform: uppercase;
            background: #eee; color: #555; display: inline-block;
        }
        .active-badge { background: var(--accent-color); color: white; }

        .trade-actions {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px; 
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1>Trade Center</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="tabs-header">
            <button class="tab-btn active" onclick="switchTab('incoming')">
                Incoming (<?php echo $count_incoming; ?>)
            </button>
            <button class="tab-btn" onclick="switchTab('sent')">
                Sent (<?php echo $count_sent; ?>)
            </button>
        </div>

        <div id="tab-incoming" class="tab-content active">
            <?php if ($count_incoming > 0): ?>
                <?php foreach ($incoming_trades as $trade): ?>
                    <div class="trade-card status-<?php echo $trade['status']; ?>">
                        <div class="partner-info">
                            <?php 
                                $hasImg = !empty($trade['profile_image']) && file_exists($trade['profile_image']);
                                $initial = strtoupper(substr($trade['partner_name'], 0, 1));
                            ?>
                            
                            <?php if ($hasImg): ?>
                                <img src="<?php echo htmlspecialchars($trade['profile_image']); ?>" class="partner-avatar">
                            <?php else: ?>
                                <div class="partner-initial"><?php echo $initial; ?></div>
                            <?php endif; ?>

                            <div>
                                <strong><?php echo htmlspecialchars($trade['partner_name']); ?></strong> 
                                wants to <?php echo strtoupper($trade['type']); ?>
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo date('d M Y', strtotime($trade['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="trade-actions">
                            <span class="trade-status-badge <?php echo ($trade['status'] == 'pending') ? 'active-badge' : ''; ?>">
                                <?php echo $trade['status']; ?>
                            </span>
                            
                            <a href="trade_details.php?id=<?php echo $trade['id']; ?>" class="btn" style="font-size: 0.8rem; padding: 5px 10px;">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999; background: #f9f9f9; border-radius: 8px;">
                    <h3>No incoming offers</h3>
                    <p>When someone wants to trade with you, it will appear here.</p>
                </div>
            <?php endif; ?>
        </div>

        <div id="tab-sent" class="tab-content">
            <?php if ($count_sent > 0): ?>
                <?php foreach ($sent_trades as $trade): ?>
                    <div class="trade-card status-<?php echo $trade['status']; ?>">
                        <div class="partner-info">
                            <?php 
                                $hasImg = !empty($trade['profile_image']) && file_exists($trade['profile_image']);
                                $initial = strtoupper(substr($trade['partner_name'], 0, 1));
                            ?>
                            
                            <?php if ($hasImg): ?>
                                <img src="<?php echo htmlspecialchars($trade['profile_image']); ?>" class="partner-avatar">
                            <?php else: ?>
                                <div class="partner-initial"><?php echo $initial; ?></div>
                            <?php endif; ?>

                            <div>
                                To: <strong><?php echo htmlspecialchars($trade['partner_name']); ?></strong> 
                                (<?php echo strtoupper($trade['type']); ?>)
                                <div style="font-size: 0.85rem; color: #666;">
                                    <?php echo date('d M Y', strtotime($trade['created_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="trade-actions">
                            <span class="trade-status-badge <?php echo ($trade['status'] == 'pending') ? 'active-badge' : ''; ?>">
                                <?php echo $trade['status']; ?>
                            </span>
                            
                            <a href="trade_details.php?id=<?php echo $trade['id']; ?>" class="btn" style="font-size: 0.8rem; padding: 5px 10px; background: #6c757d;">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #999; background: #f9f9f9; border-radius: 8px;">
                    <h3>No sent offers</h3>
                    <p>Go to the catalog or user profiles to initiate a trade.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

            document.getElementById('tab-' + tabName).classList.add('active');
            
            const buttons = document.querySelectorAll('.tab-btn');
            if (tabName === 'incoming') {
                buttons[0].classList.add('active');
            } else {
                buttons[1].classList.add('active');
            }
        }
    </script>
</body>
</html>