<?php
session_start();
require_once '../../../controller/tracking/ObjectifLongTerme_Controller.php';
require_once '../../../controller/tracking/ObjectifHebdomadaire_Controller.php';

$current_user_id = (int) ($_SESSION['user_id'] ?? 1);
$requested_user_id = (int) ($_POST['user_id'] ?? 0);

// Security check: only allow users to export their own data
if ($requested_user_id !== $current_user_id) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$longTermController = new ObjectifLongTerme_Controller();
$hebdoController = new ObjectifHebdomadaire_Controller();

// Get user's long-term goal
$allGoals = $longTermController->list_objectifs();
$currentUserGoal = null;
foreach ($allGoals as $goal) {
    if ((int) ($goal['id_user'] ?? 0) === $current_user_id) {
        $currentUserGoal = $goal;
        break;
    }
}

// Get user's weekly history
$weeklyHistory = $hebdoController->list_objectifs_by_user($current_user_id);

// Helper functions
function goal_type_label(string $type): string {
    $labels = [
        'prise_de_poids' => 'Weight gain',
        'perte_de_poids' => 'Weight loss',
        'maintien_de_poids' => 'Weight maintenance'
    ];
    return $labels[$type] ?? $type;
}

function goal_status_label(string $status): string {
    $labels = [
        'en_attente' => 'Pending',
        'en_cours' => 'In Progress',
        'termine' => 'Completed'
    ];
    return $labels[$status] ?? $status;
}

// Generate HTML content for PDF export
$html = generateHTML($currentUserGoal, $weeklyHistory);

// Send as HTML that forces download
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="tracking_export_' . date('Y-m-d_His') . '.html"');
header('Content-Length: ' . strlen($html));

echo $html;
exit;

function generateHTML($goal, $history) {
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOOVIA - Tracking Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: #102a43;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(15, 30, 50, 0.06);
            border: 1px solid rgba(30, 40, 50, 0.06);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0b7285;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 32px;
            color: #102a43;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .header p {
            color: #5b6b76;
            font-size: 14px;
        }

        .export-date {
            font-size: 12px;
            color: #8a95a1;
            margin-top: 10px;
            font-style: italic;
        }

        .section {
            margin: 30px 0;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #102a43;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0b7285;
        }

        .goal-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .goal-item {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            padding: 12px;
            border-radius: 10px;
            border: 1px solid rgba(11, 114, 133, 0.1);
            border-left: 4px solid #0b7285;
        }

        .goal-item-label {
            font-size: 12px;
            color: #5b6b76;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .goal-item-value {
            font-size: 16px;
            color: #102a43;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 13px;
        }

        th {
            background: #0b7285;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border: none;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid rgba(30, 40, 50, 0.06);
            color: #253243;
        }

        tr:nth-child(even) {
            background-color: rgba(11, 114, 133, 0.02);
        }

        tr:hover {
            background-color: rgba(11, 114, 133, 0.04);
        }

        .notes-section {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            padding: 15px;
            border-radius: 10px;
            margin-top: 15px;
            border: 1px solid rgba(11, 114, 133, 0.1);
        }

        .note-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(30, 40, 50, 0.06);
        }

        .note-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .note-date {
            font-weight: 700;
            color: #102a43;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .note-text {
            color: #253243;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(30, 40, 50, 0.06);
            text-align: center;
            font-size: 11px;
            color: #8a95a1;
        }

        .empty-message {
            background: linear-gradient(180deg, #fffbf0 0%, #fff8f3 100%);
            border: 1px solid rgba(255, 152, 0, 0.2);
            border-radius: 10px;
            padding: 15px;
            color: #7d5d2e;
            margin: 15px 0;
        }

        @media print {
            body {
                padding: 0;
                margin: 0;
                background: white;
            }
            .container {
                max-width: 100%;
                padding: 20px;
                margin: 0;
                box-shadow: none;
                border: none;
                border-radius: 0;
            }
            .section {
                page-break-inside: avoid;
            }
            .table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 FOOVIA Tracking Export</h1>
            <p>Personal Fitness & Nutrition Tracking Report</p>
            <div class="export-date">Generated on ' . date('F j, Y \a\t g:i A') . '</div>
        </div>';

    // Long-term goal section
    if (!empty($goal)) {
        $html .= '<div class="section">
            <h2 class="section-title">📍 Long-Term Goal Details</h2>
            <div class="goal-details">';

        $goalType = goal_type_label((string) ($goal['type_obj'] ?? ''));
        $html .= sprintf('
                <div class="goal-item">
                    <div class="goal-item-label">Goal Type</div>
                    <div class="goal-item-value">%s</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Status</div>
                    <div class="goal-item-value">%s</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Initial Weight</div>
                    <div class="goal-item-value">%s kg</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Target Weight</div>
                    <div class="goal-item-value">%s kg</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Start Date</div>
                    <div class="goal-item-value">%s</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">End Date</div>
                    <div class="goal-item-value">%s</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Calorie Target</div>
                    <div class="goal-item-value">%s kcal</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Protein Target</div>
                    <div class="goal-item-value">%s g</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Fat Target</div>
                    <div class="goal-item-value">%s g</div>
                </div>
                <div class="goal-item">
                    <div class="goal-item-label">Carbs Target</div>
                    <div class="goal-item-value">%s g</div>
                </div>',
            htmlspecialchars($goalType),
            htmlspecialchars(goal_status_label((string) ($goal['status_obj'] ?? ''))),
            htmlspecialchars((string) ($goal['val_init_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['val_cible_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['date_deb_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['date_fin_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['obj_cal_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['obj_prot_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['obj_fat_obj'] ?? 'N/A')),
            htmlspecialchars((string) ($goal['obj_carb_obj'] ?? 'N/A'))
        );

        $html .= '</div></div>';
    }

    // Weekly history section
    if (!empty($history)) {
        $html .= '<div class="section">
            <h2 class="section-title">📅 Weekly Tracking History</h2>';

        $html .= '<table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Weight (kg)</th>
                    <th>Calories</th>
                    <th>Protein (g)</th>
                    <th>Fat (g)</th>
                    <th>Carbs (g)</th>
                    <th>Water</th>
                    <th>Sleep (h)</th>
                    <th>Steps</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($history as $entry) {
            $html .= sprintf('
                <tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                </tr>',
                htmlspecialchars((string) ($entry['date_suiv'] ?? '')),
                htmlspecialchars((string) ($entry['poids_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['val_cal_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['val_prot_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['val_fat_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['val_carb_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['nb_verre_eau_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['nb_h_sommeil_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['nb_pas_suiv'] ?? '0')),
                htmlspecialchars((string) ($entry['status_obj_quot_suiv'] ?? 'No status'))
            );
        }

        $html .= '</tbody></table>';

        // Notes section
        $notesWithContent = array_filter($history, function ($entry) {
            return !empty($entry['note_suiv']);
        });

        if (!empty($notesWithContent)) {
            $html .= '<div class="notes-section">
                <h3 style="color: #243b7a; margin-bottom: 15px; font-size: 16px;">📝 Notes</h3>';

            foreach ($notesWithContent as $entry) {
                $html .= sprintf('
                    <div class="note-item">
                        <div class="note-date">%s</div>
                        <div class="note-text">%s</div>
                    </div>',
                    htmlspecialchars((string) ($entry['date_suiv'] ?? 'Unknown Date')),
                    nl2br(htmlspecialchars((string) ($entry['note_suiv'] ?? '')))
                );
            }

            $html .= '</div>';
        }

        $html .= '</div>';
    } else {
        $html .= '<div class="empty-message">No weekly tracking history found yet. Start logging your daily entries to see the history here.</div>';
    }

    // Footer
    $html .= '<div class="footer">
        <p>Generated on ' . date('Y-m-d H:i:s') . ' by FOOVIA Tracking System</p>
        <p>© 2026 FOOVIA - All rights reserved</p>
        <p style="margin-top: 10px; font-size: 10px;">This is a personal health record. Please keep it confidential and secure.</p>
    </div>';

    $html .= '</div>
</body>
</html>';

    return $html;
}
