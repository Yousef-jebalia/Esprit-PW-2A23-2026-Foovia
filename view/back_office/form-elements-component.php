<?php
session_start();
include '../../controller/ObjectifLongTerme_Controller.php';

// Initialisation des variables
$error_message = '';
$success_message = '';
$controller = new ObjectifLongTerme_Controller();
$user_id = $_SESSION['user_id'] ?? 1; // À adapter selon votre système d'authentification
$next_objectif_id = $controller->get_next_objectif_id();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $generated_id_obj = $controller->get_next_objectif_id();

    // Récupération des données du formulaire
    $data = [
        'id_obj' => $generated_id_obj,
        'id_user' => $user_id,
        'type_obj' => $_POST['type_obj'] ?? null,
        'val_init_obj' => $_POST['val_init_obj'] ?? null,
        'val_cible_obj' => $_POST['val_cible_obj'] ?? null,
        'date_deb_obj' => $_POST['date_deb_obj'] ?? null,
        'date_fin_obj' => $_POST['date_fin_obj'] ?? null,
        'status_obj' => $_POST['status_obj'] ?? 'en_attente',
        'frequency_rappel_obj' => $_POST['frequency_rappel_obj'] ?? null,
        'consistancy_sport_obj' => $_POST['consistancy_sport_obj'] ?? 0,
        'consistency_alim_obj' => $_POST['consistency_alim_obj'] ?? 0,
        'obj_cal_obj' => $_POST['obj_cal_obj'] ?? null,
        'obj_fat_obj' => $_POST['obj_fat_obj'] ?? null,
        'obj_prot_obj' => $_POST['obj_prot_obj'] ?? null,
        'obj_carb_obj' => $_POST['obj_carb_obj'] ?? null
    ];
    $next_objectif_id = (int) $data['id_obj'];
    
    // Validation des données
    $errors = [];
    
    // Check required fields
    if (empty($data['id_obj'])) $errors[] = "Goal ID is required";
    if (empty($data['id_user'])) $errors[] = "User ID is required";
    if (empty($data['type_obj'])) $errors[] = "Goal type is required";
    if (empty($data['val_init_obj'])) $errors[] = "Initial value is required";
    if (empty($data['val_cible_obj'])) $errors[] = "Target value is required";
    if (empty($data['date_deb_obj'])) $errors[] = "Start date is required";
    if (empty($data['date_fin_obj'])) $errors[] = "End date is required";
    
    // Vérification des valeurs positives
    $positive_fields = ['val_cible_obj', 'val_init_obj', 'obj_cal_obj', 'obj_fat_obj', 'obj_prot_obj', 'obj_carb_obj', 'frequency_rappel_obj'];
    foreach ($positive_fields as $field) {
        if (!empty($data[$field]) && $data[$field] <= 0) {
            $errors[] = "The field must be strictly positive";
        }
    }
    
    // Vérification des dates
    if (!empty($data['date_deb_obj']) && !empty($data['date_fin_obj'])) {
        $date_deb = new DateTime($data['date_deb_obj']);
        $date_fin = new DateTime($data['date_fin_obj']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($date_deb < $today) $errors[] = "The start date cannot be earlier than today";
        if ($date_deb > $date_fin) $errors[] = "The start date cannot be later than the end date";
        
        $diff = $date_deb->diff($date_fin);
        if ($diff->days < 30) $errors[] = "The minimum goal duration is 30 days";
    }
    
    // Vérification valeur cible selon type
    if (!empty($data['type_obj']) && !empty($data['val_cible_obj']) && !empty($data['val_init_obj'])) {
        $type = $data['type_obj'];
        $val_cible = floatval($data['val_cible_obj']);
        $val_init = floatval($data['val_init_obj']);
        
        if ($type == 'prise_de_poids' && $val_cible <= $val_init) {
            $errors[] = "For weight gain, the target value must be greater than the initial value";
        }
        if ($type == 'perte_de_poids' && $val_cible >= $val_init) {
            $errors[] = "For weight loss, the target value must be lower than the initial value";
        }
        if ($type == 'maintien_de_poids' && abs($val_cible - $val_init) > 0.5) {
            $errors[] = "For weight maintenance, the target value must be close to the initial value (+/- 0.5)";
        }
    }

    if (empty($errors) && !empty($data['date_deb_obj'])) {
        $today = new DateTime('today');
        $start_date = new DateTime($data['date_deb_obj']);
        $data['status_obj'] = $start_date < $today ? 'en_cours' : 'en_attente';
    }
    
    // Si pas d'erreurs, insertion en base de données
    if (empty($errors)) {
        try {
            // Création de l'objet ObjectifLongTerme
            $objectif = new ObjectifLongTerme(
                $data['id_obj'],
                $data['id_user'],
                $data['type_obj'],
                floatval($data['val_cible_obj']),
                floatval($data['val_init_obj']),
                $data['date_deb_obj'],
                $data['date_fin_obj'],
                $data['status_obj'],
                intval($data['frequency_rappel_obj'] ?? 0),
                intval($data['consistancy_sport_obj'] ?? 0),
                intval($data['consistency_alim_obj'] ?? 0),
                floatval($data['obj_cal_obj'] ?? 0),
                floatval($data['obj_fat_obj'] ?? 0),
                floatval($data['obj_prot_obj'] ?? 0),
                floatval($data['obj_carb_obj'] ?? 0)
            );
            
            // Modification de la méthode add_objectif pour accepter les données
            $sql = "INSERT INTO objectiflongterme (id_obj, id_user, type_obj, val_cible_obj, val_init_obj, date_deb_obj, date_fin_obj, status_obj, frequency_rappel_obj, consistancy_sport_obj, consistency_alim_obj, obj_cal_obj, obj_fat_obj, obj_prot_obj, obj_carb_obj) 
                    VALUES (:id_obj, :id_user, :type_obj, :val_cible_obj, :val_init_obj, :date_deb_obj, :date_fin_obj, :status_obj, :frequency_rappel_obj, :consistancy_sport_obj, :consistency_alim_obj, :obj_cal_obj, :obj_fat_obj, :obj_prot_obj, :obj_carb_obj)";
            
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->execute([
                'id_obj' => $objectif->getIdObj(),
                'id_user' => $objectif->getIdUser(),
                'type_obj' => $objectif->getTypeObj(),
                'val_cible_obj' => $objectif->getValCibleObj(),
                'val_init_obj' => $objectif->getValInitObj(),
                'date_deb_obj' => $objectif->getDateDebObj(),
                'date_fin_obj' => $objectif->getDateFinObj(),
                'status_obj' => $objectif->getStatusObj(),
                'frequency_rappel_obj' => $objectif->getFrequencyRappelObj(),
                'consistancy_sport_obj' => $objectif->getConsistancySportObj(),
                'consistency_alim_obj' => $objectif->getConsistencyAlimObj(),
                'obj_cal_obj' => $objectif->getObjCalObj(),
                'obj_fat_obj' => $objectif->getObjFatObj(),
                'obj_prot_obj' => $objectif->getObjProtObj(),
                'obj_carb_obj' => $objectif->getObjCarbObj()
            ]);

            header('Location: ../front_office/index.html');
            exit;
            
        } catch (Exception $e) {
            $error_message = "❌ Error while inserting: " . $e->getMessage();
        }
    } else {
        $error_message = implode("<br>❌ ", $errors);
        $error_message = "❌ " . $error_message;
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add a long-term goal</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../front_office/css/vendor.css">
    <link rel="stylesheet" type="text/css" href="../front_office/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">

    <style>
        body {
            background:
                radial-gradient(circle at 10% 10%, rgba(245, 200, 66, .24) 0%, rgba(245, 200, 66, 0) 48%),
                radial-gradient(circle at 90% 90%, rgba(75, 174, 82, .16) 0%, rgba(75, 174, 82, 0) 45%),
                var(--page-bg);
            font-family: 'DM Sans', sans-serif;
            color: var(--page-text);
        }

        .survey-wrap {
            max-width: 1040px;
            margin: 0 auto;
        }

        .survey-intro {
            margin-bottom: 1.1rem;
            border-radius: 22px;
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, .16) 0%, rgba(245, 200, 66, 0) 58%),
                linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
            border: 1.5px solid var(--surface-border);
            box-shadow: 0 18px 36px rgba(17, 16, 8, 0.08);
            padding: 1.1rem 1.25rem;
        }

        .survey-intro small {
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.13em;
            font-weight: 700;
            font-size: 0.7rem;
            color: var(--green);
            margin-bottom: 0.38rem;
            font-family: 'Syne', sans-serif;
        }

        .survey-intro h2 {
            margin: 0;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.42rem;
            color: var(--panel-text);
        }

        .form-shell {
            border: 1.5px solid var(--surface-border);
            border-radius: 24px;
            box-shadow: 0 22px 44px rgba(17, 16, 8, 0.1);
            background:
                radial-gradient(120% 120% at 0% 0%, rgba(245, 200, 66, .18) 0%, rgba(245, 200, 66, 0) 58%),
                linear-gradient(160deg, var(--surface) 0%, var(--surface-2) 100%);
        }

        .form-section-title {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--green);
            margin-bottom: 0.9rem;
            margin-top: 1.15rem;
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            padding-bottom: 0.45rem;
            border-bottom: 1px solid rgba(17,16,8,.08);
        }

        .form-label {
            font-size: 0.86rem;
            font-weight: 700;
            color: var(--panel-text);
            margin-bottom: 0.38rem;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            border: 1px solid rgba(17,16,8,.16);
            padding: 0.78rem 0.88rem;
            box-shadow: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background: var(--surface);
            color: var(--panel-text);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(75, 174, 82, 0.16);
        }

        .form-control[readonly] {
            background: rgba(17,16,8,.05);
            color: var(--panel-muted);
        }

        #objectifForm .btn-primary {
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--orange) 0%, var(--red) 100%);
            padding: 0.72rem 1.7rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            font-family: 'Syne', sans-serif;
            box-shadow: 0 8px 24px rgba(217,79,0,.26);
        }

        #objectifForm .btn-primary:hover {
            filter: brightness(0.96);
        }

        #objectifForm .btn-primary:focus-visible {
            box-shadow: 0 0 0 3px rgba(245, 200, 66, 0.22), 0 8px 24px rgba(217,79,0,.26);
        }

        .survey-intro h2,
        .form-shell {
            position: relative;
            overflow: hidden;
        }

        .survey-intro::after,
        .form-shell::after {
            content: '';
            position: absolute;
            inset: auto -18% -20% auto;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(75, 174, 82, 0.08);
            pointer-events: none;
        }

        .alert {
            border-radius: 12px;
            border: 0;
        }
    </style>
</head>

<body>
    <section class="py-5">
        <div class="container-lg survey-wrap">
            <div class="survey-intro">
                <small>Long term goals</small>
                <h2>Create a new objective</h2>
            </div>
            <div class="form-shell bg-white p-4 p-md-5">
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <form id="objectifForm" method="POST" action="">
                    <h6 class="form-section-title">Identification</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="id_obj">Goal ID</label>
                            <input type="number" class="form-control" id="id_obj" name="id_obj" value="<?php echo htmlspecialchars((string) $next_objectif_id); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="id_user">User ID</label>
                            <input type="text" class="form-control" id="id_user" name="id_user" value="<?php echo $user_id; ?>" readonly>
                        </div>
                    </div>

                    <h6 class="form-section-title mt-4">Goal type and values</h6>
                    <div class="mb-3">
                        <label class="form-label" for="type_obj">Goal type</label>
                        <select class="form-select" id="type_obj" name="type_obj">
                            <option value="">Select a type</option>
                            <option value="prise_de_poids">Weight gain</option>
                            <option value="perte_de_poids">Weight loss</option>
                            <option value="maintien_de_poids">Weight maintenance</option>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="val_init_obj">Initial value (kg)</label>
                            <input type="number" class="form-control" id="val_init_obj" name="val_init_obj" step="0.01" min="0.01" placeholder="Ex: 75.5" required>
                            <small id="initError" class="form-text text-danger" style="display:none;"></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="val_cible_obj">Target value (kg)</label>
                            <input type="number" class="form-control" id="val_cible_obj" name="val_cible_obj" step="0.01" min="0.01" placeholder="Ex: 68.0" required>
                            <small id="cibleError" class="form-text text-danger" style="display:none;"></small>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="status_obj_display">Status</label>
                        <input type="text" class="form-control" id="status_obj_display" value="pending" readonly>
                        <input type="hidden" id="status_obj" name="status_obj" value="en_attente">
                    </div>

                    <h6 class="form-section-title mt-4">Period</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="date_deb_obj">Start date</label>
                            <input type="date" class="form-control" id="date_deb_obj" name="date_deb_obj" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="date_fin_obj">End date</label>
                            <input type="date" class="form-control" id="date_fin_obj" name="date_fin_obj" required>
                            <small id="dateError" class="form-text text-danger" style="display:none;"></small>
                        </div>
                    </div>

                    <h6 class="form-section-title mt-4">Reminders and tracking</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="frequency_rappel_obj">Reminder frequency (days)</label>
                            <input type="number" class="form-control" id="frequency_rappel_obj" name="frequency_rappel_obj" min="1" placeholder="Ex: 7">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="consistancy_sport_obj">Sport consistency</label>
                            <input type="number" class="form-control" id="consistancy_sport_obj" name="consistancy_sport_obj" value="0" readonly>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="consistency_alim_obj">Diet consistency</label>
                        <input type="number" class="form-control" id="consistency_alim_obj" name="consistency_alim_obj" value="0" readonly>
                    </div>

                    <h6 class="form-section-title mt-4">Nutrition goals</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="obj_cal_obj">Calories (kcal)</label>
                            <input type="number" class="form-control" id="obj_cal_obj" name="obj_cal_obj" min="1" step="1" placeholder="Ex: 2000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="obj_fat_obj">Fat (g)</label>
                            <input type="number" class="form-control" id="obj_fat_obj" name="obj_fat_obj" min="0.1" step="0.1" placeholder="Ex: 65">
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label" for="obj_prot_obj">Protein (g)</label>
                            <input type="number" class="form-control" id="obj_prot_obj" name="obj_prot_obj" min="0.1" step="0.1" placeholder="Ex: 150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="obj_carb_obj">Carbs (g)</label>
                            <input type="number" class="form-control" id="obj_carb_obj" name="obj_carb_obj" min="0.1" step="0.1" placeholder="Ex: 250">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>


    <script>
        // Récupération des éléments du formulaire
        const typeObjSelect = document.querySelector('select[name="type_obj"]');
        const valCibleInput = document.getElementById('val_cible_obj');
        const valInitInput = document.getElementById('val_init_obj');
        const cibleError = document.getElementById('cibleError');
        const initError = document.getElementById('initError');
        
        // Éléments pour les dates
        const dateDebInput = document.getElementById('date_deb_obj');
        const dateFinInput = document.getElementById('date_fin_obj');
        const dateErrorSpan = document.getElementById('dateError');
        
        // Éléments pour les champs positifs
        const positiveFields = ['val_cible_obj', 'val_init_obj', 'obj_cal_obj', 'obj_fat_obj', 'obj_prot_obj', 'obj_carb_obj', 'frequency_rappel_obj'];
        
        // ============ FONCTION POUR DÉFINIR LA DATE MINIMALE (DATE SYSTÈME) ============
        function setMinDates() {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const todayString = year + '-' + month + '-' + day;
            
            if (dateDebInput) {
                dateDebInput.setAttribute('min', todayString);
            }
            if (dateFinInput) {
                dateFinInput.setAttribute('min', todayString);
            }
        }
        
        // ============ FONCTION DE VALIDATION DE LA VALEUR CIBLE ============
        function validateValeurCible() {
            const typeObj = typeObjSelect ? typeObjSelect.value : '';
            const valCible = parseFloat(valCibleInput ? valCibleInput.value : 0);
            const valInit = parseFloat(valInitInput ? valInitInput.value : 0);
            
            if (valCibleInput) {
                valCibleInput.style.borderColor = '';
                valCibleInput.style.borderWidth = '';
            }
            if (cibleError) cibleError.style.display = 'none';
            if (initError) initError.style.display = 'none';
            
            if (isNaN(valCible) || isNaN(valInit)) {
                if (valCibleInput && (isNaN(valCible) || valCibleInput.value === '')) {
                    valCibleInput.style.borderColor = '#ffc107';
                    valCibleInput.style.borderWidth = '2px';
                }
                if (valInitInput && (isNaN(valInit) || valInitInput.value === '')) {
                    valInitInput.style.borderColor = '#ffc107';
                    valInitInput.style.borderWidth = '2px';
                }
                if (typeObj === '') {
                    if (cibleError) {
                        cibleError.textContent = '⚠️ Please select a goal type first.';
                        cibleError.style.display = 'block';
                    }
                }
                return false;
            }
            
            let isValid = true;
            let errorMsg = '';
            
            if (typeObj === 'prise_de_poids') {
                if (valCible <= valInit) {
                    isValid = false;
                    errorMsg = '⚠️ For weight gain, the target value must be HIGHER than the initial value (' + valInit + ').';
                }
            } else if (typeObj === 'perte_de_poids') {
                if (valCible >= valInit) {
                    isValid = false;
                    errorMsg = '⚠️ For weight loss, the target value must be LOWER than the initial value (' + valInit + ').';
                }
            } else if (typeObj === 'maintien_de_poids') {
                if (Math.abs(valCible - valInit) > 0.5) {
                    isValid = false;
                    errorMsg = '⚠️ For weight maintenance, the target value must be close to the initial value (' + valInit + ') within +/- 0.5.';
                }
            } else if (typeObj === '') {
                isValid = false;
                errorMsg = '⚠️ Please select a goal type.';
            }
            
            if (!isValid) {
                if (cibleError) {
                    cibleError.textContent = errorMsg;
                    cibleError.style.display = 'block';
                }
                if (valCibleInput) {
                    valCibleInput.style.borderColor = '#dc3545';
                    valCibleInput.style.borderWidth = '2px';
                    valCibleInput.setCustomValidity(errorMsg);
                }
                if (valInitInput) {
                    valInitInput.style.borderColor = '#dc3545';
                    valInitInput.style.borderWidth = '2px';
                }
            } else {
                if (cibleError) cibleError.style.display = 'none';
                if (valCibleInput) {
                    valCibleInput.style.borderColor = '#28a745';
                    valCibleInput.style.borderWidth = '2px';
                    valCibleInput.setCustomValidity('');
                }
                if (valInitInput) {
                    valInitInput.style.borderColor = '#28a745';
                    valInitInput.style.borderWidth = '2px';
                }
            }
            
            return isValid;
        }
        
        // ============ FONCTION DE VALIDATION DES CHAMPS POSITIFS ============
        function validatePositiveFields() {
            let allValid = true;
            
            positiveFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && field.value !== '') {
                    const value = parseFloat(field.value);
                    if (isNaN(value) || value <= 0) {
                        field.style.borderColor = '#dc3545';
                        field.style.borderWidth = '2px';
                        allValid = false;
                    } else {
                        field.style.borderColor = '#28a745';
                        field.style.borderWidth = '2px';
                    }
                } else if (field && field.value === '') {
                    field.style.borderColor = '#ffc107';
                    field.style.borderWidth = '2px';
                    allValid = false;
                }
            });
            
            return allValid;
        }
        
        
        // ============ VALIDATION DES DATES ============
        function validateDates() {
            if (!dateDebInput || !dateFinInput || !dateErrorSpan) return true;
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const dateDeb = new Date(dateDebInput.value);
            const dateFin = new Date(dateFinInput.value);
            
            dateDebInput.style.borderColor = '';
            dateFinInput.style.borderColor = '';
            dateErrorSpan.style.display = 'none';
            
            if (!dateDebInput.value || !dateFinInput.value) {
                if (!dateDebInput.value) dateDebInput.style.borderColor = '#ffc107';
                if (!dateFinInput.value) dateFinInput.style.borderColor = '#ffc107';
                return false;
            }
            
            let isValid = true;
            let errorMsg = '';
            
            // Vérifier que date début n'est pas antérieure à aujourd'hui
            if (dateDeb < today) {
                isValid = false;
                errorMsg = '❌ The start date cannot be earlier than today.';
                dateDebInput.style.borderColor = '#dc3545';
                dateFinInput.style.borderColor = '#dc3545';
            }
            // Vérifier que date début <= date fin
            else if (dateDeb > dateFin) {
                isValid = false;
                errorMsg = '❌ The start date cannot be later than the end date.';
                dateDebInput.style.borderColor = '#dc3545';
                dateFinInput.style.borderColor = '#dc3545';
            } 
            // Vérifier la durée minimale d'un mois (30 jours)
            else {
                const diffTime = dateFin - dateDeb;
                const diffDays = diffTime / (1000 * 60 * 60 * 24);
                
                if (diffDays < 30) {
                    isValid = false;
                    errorMsg = '❌ The minimum goal duration is one month (30 days).';
                    dateDebInput.style.borderColor = '#dc3545';
                    dateFinInput.style.borderColor = '#dc3545';
                } else {
                    dateDebInput.style.borderColor = '#28a745';
                    dateFinInput.style.borderColor = '#28a745';
                }
            }
            
            if (!isValid) {
                dateErrorSpan.textContent = errorMsg;
                dateErrorSpan.style.display = 'block';
                dateFinInput.setCustomValidity(errorMsg);
            } else {
                dateErrorSpan.style.display = 'none';
                dateFinInput.setCustomValidity('');
            }
            
            return isValid;
        }
        
        // ============ VALIDATION GLOBALE ============
        function validateAll() {
            const isCibleValid = validateValeurCible();
            const isDatesValid = validateDates();
            const isPositiveValid = validatePositiveFields();
            
            return isCibleValid && isDatesValid && isPositiveValid ;
        }


        // ============ CONSERVATION DES VALEURS APRÈS SOUMISSION ============
        // Cette fonction permet de garder les valeurs saisies si le formulaire est refusé
        function keepFormValues() {
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($error_message)): ?>
                const fields = {
                    'type_obj': '<?php echo addslashes($_POST['type_obj'] ?? ''); ?>',
                    'val_init_obj': '<?php echo addslashes($_POST['val_init_obj'] ?? ''); ?>',
                    'val_cible_obj': '<?php echo addslashes($_POST['val_cible_obj'] ?? ''); ?>',
                    'date_deb_obj': '<?php echo addslashes($_POST['date_deb_obj'] ?? ''); ?>',
                    'date_fin_obj': '<?php echo addslashes($_POST['date_fin_obj'] ?? ''); ?>',
                    'frequency_rappel_obj': '<?php echo addslashes($_POST['frequency_rappel_obj'] ?? ''); ?>',
                    'obj_cal_obj': '<?php echo addslashes($_POST['obj_cal_obj'] ?? ''); ?>',
                    'obj_fat_obj': '<?php echo addslashes($_POST['obj_fat_obj'] ?? ''); ?>',
                    'obj_prot_obj': '<?php echo addslashes($_POST['obj_prot_obj'] ?? ''); ?>',
                    'obj_carb_obj': '<?php echo addslashes($_POST['obj_carb_obj'] ?? ''); ?>'
                };
        
                for (const [id, value] of Object.entries(fields)) {
                    const element = document.getElementById(id);
                    if (element && value) {
                        element.value = value;
                    }
                }
            <?php endif; ?>
        }

        
        
        // ============ AJOUT DES ÉCOUTEURS D'ÉVÉNEMENTS ============
        if (typeObjSelect) {
            typeObjSelect.addEventListener('change', validateValeurCible);
        }
        if (valCibleInput) {
            valCibleInput.addEventListener('input', validateValeurCible);
            valCibleInput.addEventListener('keyup', validateValeurCible);
            valCibleInput.addEventListener('blur', validateValeurCible);
        }
        if (valInitInput) {
            valInitInput.addEventListener('input', validateValeurCible);
            valInitInput.addEventListener('keyup', validateValeurCible);
            valInitInput.addEventListener('blur', validateValeurCible);
        }
        
        positiveFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', validatePositiveFields);
                field.addEventListener('blur', validatePositiveFields);
            }
        });
        
        if (dateDebInput) {
            dateDebInput.addEventListener('change', validateDates);
            dateDebInput.addEventListener('blur', validateDates);
        }
        if (dateFinInput) {
            dateFinInput.addEventListener('change', validateDates);
            dateFinInput.addEventListener('blur', validateDates);
        }
        
        // ============ VALIDATION AVANT SOUMISSION ============
        const form = document.getElementById('objectifForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const isValid = validateAll();
                
                if (!isValid) {
                    e.preventDefault();
                    alert('❌ Please fix the errors in the form (red or orange fields).');
                } else {
                    alert('✅ Valid form! Submitting...');
                }
            });
        }
        
        // ============ VALIDATION INITIALE AU CHARGEMENT ============
        document.addEventListener('DOMContentLoaded', function() {
            setMinDates();  
            validateAll();
            keepFormValues();
        });

    </script>



    <script src="../front_office/js/jquery-1.11.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <script src="../front_office/js/plugins.js"></script>
    <script src="../front_office/js/script.js"></script>
</body>

</html>