<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/admin.module.css">
                <div class="glass-card" style="padding: 1.25rem;">
                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Notification Status</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php
                        $notificationStats = [
                            ['label' => 'Emails Sent Today', 'value' => '234', 'status' => 'success', 'icon' => 'check-circle'],
                            ['label' => 'Failed Deliveries', 'value' => '3', 'status' => 'error', 'icon' => 'x-circle'],
                            ['label' => 'Pending Queue', 'value' => '12', 'status' => 'warning', 'icon' => 'clock'],
                        ];

                        foreach ($notificationStats as $stat): 
                            $iconColor = '';
                            switch ($stat['status']) {
                                case 'success': $iconColor = 'color: #16a34a;'; break;
                                case 'error': $iconColor = 'color: #dc2626;'; break;
                                case 'warning': $iconColor = 'color: #ca8a04;'; break;
                            }
                        ?>
                        <div class="glass-subtle" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-radius: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <i data-lucide="<?php echo $stat['icon']; ?>" style="width: 1rem; height: 1rem; <?php echo $iconColor; ?>"></i>
                                <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);"><?php echo $stat['label']; ?></p>
                            </div>
                            <p style="font-size: 1.125rem; font-weight: 700; color: #000;"><?php echo $stat['value']; ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
