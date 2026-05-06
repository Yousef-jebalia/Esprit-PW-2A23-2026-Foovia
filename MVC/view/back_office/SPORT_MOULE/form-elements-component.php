<?php
require_once __DIR__ . '/../../../Model/config.php';

$db = config::getConnexion();
$stmt = $db->query("SELECT * FROM exercice ORDER BY id_ex DESC");
$exercises = $stmt->fetchAll();

$stmt_categories = $db->query("SELECT id_cat, name_cat FROM work_categorie ORDER BY name_cat ASC");
$workCategories = $stmt_categories->fetchAll();
$selectedCategoryFromQuery = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

$stmt_workout = $db->query(
    "SELECT w.*, wc.name_cat
     FROM workout w
     LEFT JOIN work_categorie wc ON wc.id_cat = w.id_cat
     ORDER BY w.id_work DESC"
);
$workouts = $stmt_workout->fetchAll();

$stmt_workout_exercises = $db->query(
    "SELECT b.id_work, b.id_ex, b.sets, b.weight, b.`time`, e.name_ex, e.type_ex
     FROM belong b
     INNER JOIN exercice e ON e.id_ex = b.id_ex
     ORDER BY b.id_work DESC, b.id_ex ASC"
);
$workoutExerciseRows = $stmt_workout_exercises->fetchAll(PDO::FETCH_ASSOC);

$workoutExercisesByWorkout = [];
foreach ($workoutExerciseRows as $row) {
    $workoutId = (int)$row['id_work'];
    $typeEx = strtolower(trim((string)$row['type_ex']));
    $isCardio = ($typeEx === 'cardio');

    $workoutExercisesByWorkout[$workoutId][] = [
        'id_ex' => (int)$row['id_ex'],
        'name' => (string)$row['name_ex'],
        'type_ex' => $typeEx,
        'sets' => $isCardio ? 0 : max(1, (int)$row['sets']),
        'reps' => $isCardio ? 0 : max(1, (int)$row['time']),
        'weight' => $isCardio ? 0 : max(0, (float)$row['weight']),
        'time' => $isCardio ? max(1, (int)$row['time']) : 0,
    ];
}
?>






<!DOCTYPE html>
<html lang="en">

<head>
    <title>Material Able bootstrap admin template by Codedthemes</title>
    <!-- HTML5 Shim and Respond.js IE10 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 10]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="keywords" content="bootstrap, bootstrap admin template, admin theme, admin dashboard, dashboard template, admin template, responsive" />
    <meta name="author" content="Codedthemes" />
    <!-- Favicon icon -->
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">
    <!-- waves.css -->
    <link rel="stylesheet" href="assets/pages/waves/css/waves.min.css" type="text/css" media="all">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <!-- themify-icons line icon -->
    <link rel="stylesheet" type="text/css" href="assets/icon/themify-icons/themify-icons.css">
    <!-- ico font -->
    <link rel="stylesheet" type="text/css" href="assets/icon/icofont/css/icofont.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="assets/icon/font-awesome/css/font-awesome.min.css">
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="assets/css/jquery.mCustomScrollbar.css">
    <!-- FOOVIA Dashboard CSS -->
    <link rel="stylesheet" type="text/css" href="assets/css/dashboard-layout.css">

</head>


<script>
let selectedWorkoutExercises = [];

function updateSelectedExercisesInput() {
    document.getElementById('selected_exercises').value = JSON.stringify(
        selectedWorkoutExercises.map(function(item) {
            return {
                id_ex: item.id_ex,
                type_ex: item.type_ex,
                sets: item.sets,
                reps: item.reps,
                weight: item.weight,
                time: item.time
            };
        })
    );
}

function renderSelectedExercisesSummary() {
    const summary = document.getElementById('selected-exercises-summary');
    if (selectedWorkoutExercises.length === 0) {
        summary.innerHTML = 'No exercises selected';
        return;
    }

    summary.innerHTML = selectedWorkoutExercises.map(function(item) {
        if (item.type_ex === 'cardio') {
            return item.name + ' (cardio: time ' + item.time + ' min)';
        }
        return item.name + ' (sets: ' + item.sets + ', reps: ' + item.reps + ', weight: ' + item.weight + ')';
    }).join('<br>');
}

function syncExerciseSelectorWithSelection() {
    const selectedById = new Map();
    selectedWorkoutExercises.forEach(function(item) {
        selectedById.set(Number(item.id_ex), item);
    });

    document.querySelectorAll('.workout-exercise-row').forEach(function(row) {
        const checkbox = row.querySelector('.workout-ex-checkbox');
        const setsInput = row.querySelector('.workout-ex-sets');
        const repsInput = row.querySelector('.workout-ex-reps');
        const weightInput = row.querySelector('.workout-ex-weight');
        const timeInput = row.querySelector('.workout-ex-time');

        if (!checkbox || !setsInput || !repsInput || !weightInput || !timeInput) {
            return;
        }

        const idEx = Number(checkbox.value);
        const item = selectedById.get(idEx);

        if (!item) {
            checkbox.checked = false;
            setsInput.value = 3;
            repsInput.value = 10;
            weightInput.value = 0;
            timeInput.value = 30;
            row.querySelectorAll('.workout-ex-config').forEach(function(input) {
                input.disabled = true;
            });
            return;
        }

        checkbox.checked = true;
        setsInput.value = item.sets;
        repsInput.value = item.reps;
        weightInput.value = item.weight;
        timeInput.value = item.time;
        toggleExerciseConfig(checkbox);
    });
}

function validateForm() {//exercise form validation
    const name = document.getElementById('ex_name').value.trim();
    const type = document.getElementById('ex_type').value;
    const muscles = Array.from(document.querySelectorAll('input[name="ex_target_muscle[]"]:checked'));
    const calories = Number(document.getElementById('ex_calories').value);
    const fatigue = Number(document.getElementById('ex_fatigue').value);

    if (name.length < 3) {
        alert('Exercise name must contain at least 3 characters.');
        return false;
    }

    if (!type) {
        alert('Please select an exercise type.');
        return false;
    }

    if (muscles.length === 0) {
        alert('Please select at least one target muscle.');
        return false;
    }

    if (muscles.length > 3) {
        alert('You can select a maximum of 3 muscles.');
        return false;
    }

    if (type === 'isolation' && muscles.length > 1) {
        alert('Isolation exercise must target only one muscle.');
        return false;
    }

    if (!Number.isFinite(calories) || calories < 0 || calories > 100) {
        alert('Calories per rep must be a number between 0 and 100.');
        return false;
    }

    if (!Number.isFinite(fatigue) || fatigue < 0 || fatigue > 10) {
        alert('Fatigue ratio must be a number between 0 and 10.');
        return false;
    }
    if (document.getElementById('ex_description').value.trim() === '') {
        alert('Please provide a description for the exercise.');
        return false;
    }
    if (document.getElementById('ex_gif').value.trim() === '') {
        alert('Please provide a GIF URL for the exercise.');
        return false;
    }

    return true;
}



function validateWorkoutForm() {
    const name = document.getElementById('work_name').value.trim();
    const duree = Number(document.getElementById('work_duree').value);
    const selectedExercisesRaw = document.getElementById('selected_exercises').value.trim();
    const selectedCategoryId = document.getElementById('work_id_cat').value;
    const newCategoryName = document.getElementById('new_work_categorie').value.trim();
    const workoutPicture = document.getElementById('work_picture').value.trim();
    const errors = [];

    if (!name) {
        errors.push('Workout name cannot be empty.');
    } else if (name.length < 3) {
        errors.push('Workout name must contain at least 3 characters.');
    }

    if (!String(document.getElementById('work_duree').value).trim()) {
        errors.push('Workout duration cannot be empty.');
    } else if (!Number.isFinite(duree) || duree < 15 || duree > 180) {
        errors.push('Workout duration must be between 15 and 180 minutes.');
    }

    if (!selectedCategoryId && !newCategoryName) {
        errors.push('Please select or create a category.');
    }

    if (selectedExercisesRaw === '') {
        errors.push('Please select at least one exercise for this workout.');
    }

    if (!workoutPicture) {
        errors.push('Please upload a workout image.');
    }

    try {
        const parsed = JSON.parse(selectedExercisesRaw);
        if (!Array.isArray(parsed) || parsed.length === 0) {
            errors.push('Please select at least one exercise for this workout.');
        }
    } catch (e) {
        errors.push('Selected exercises are invalid. Please select again.');
    }

    if (errors.length > 0) {
        alert(errors.join('\n'));
        return false;
    }

    return true;
}

function validateWorkoutCategoryName(categoryName) {
    const name = String(categoryName || '').trim();

    if (name && (name.length <= 2 || name.length >= 30)) {
        alert('Category name length must be greater than 3 and less than 30.');
        return false;
    }

    return true;
}

function submitNewWorkoutCategory() {
    const input = document.getElementById('new_work_categorie');
    const hiddenInput = document.getElementById('standalone_new_work_categorie');
    const form = document.getElementById('standalone-category-form');

    if (!input || !hiddenInput || !form) {
        return;
    }

    const categoryName = input.value.trim();
    if (!validateWorkoutCategoryName(categoryName)) 
        
        {
        input.focus();
        return;
    }

    hiddenInput.value = categoryName;
    form.submit();
}

function deleteWorkoutCategory(idCat, nameCat) {
    const form = document.getElementById('standalone-delete-category-form');
    const hiddenId = document.getElementById('standalone_delete_id_cat');

    if (!form || !hiddenId) {
        return;
    }

    if (!confirm('Delete category "' + nameCat + '"?')) {
        return;
    }

    hiddenId.value = String(idCat);
    form.submit();
}

function fillEditWorkoutForm(id, name, duree, idCat, exercisesPayload) {
    const normalizedCategory = (idCat === null || idCat === undefined) ? '' : String(idCat);
    let parsedExercises = [];

    if (Array.isArray(exercisesPayload)) {
        parsedExercises = exercisesPayload;
    } else if (typeof exercisesPayload === 'string' && exercisesPayload.trim() !== '') {
        try {
            const decoded = JSON.parse(exercisesPayload);
            if (Array.isArray(decoded)) {
                parsedExercises = decoded;
            }
        } catch (e) {
            parsedExercises = [];
        }
    }

    document.getElementById('work-form-action').value = 'update';
    document.getElementById('work-edit-id').value = id;
    document.getElementById('work_name').value = name;
    document.getElementById('work_duree').value = duree;
    document.getElementById('work_id_cat').value = normalizedCategory;
    document.getElementById('new_work_categorie').value = '';
    selectedWorkoutExercises = parsedExercises.map(function(item) {
        const typeEx = String(item.type_ex || '').toLowerCase();
        const isCardio = typeEx === 'cardio';

        return {
            id_ex: Number(item.id_ex),
            name: item.name || ('Exercise #' + item.id_ex),
            type_ex: typeEx,
            sets: isCardio ? 0 : Math.max(1, Number(item.sets || 1)),
            reps: isCardio ? 0 : Math.max(1, Number(item.reps || 1)),
            weight: isCardio ? 0 : Math.max(0, Number(item.weight || 0)),
            time: isCardio ? Math.max(1, Number(item.time || 1)) : 0
        };
    }).filter(function(item) {
        return Number.isFinite(item.id_ex) && item.id_ex > 0;
    });

    updateSelectedExercisesInput();
    renderSelectedExercisesSummary();
    syncExerciseSelectorWithSelection();

    document.getElementById('workout-form').scrollIntoView({ behavior: 'smooth' });
}

function openExerciseSelectorModal() {
    syncExerciseSelectorWithSelection();
    document.getElementById('exercise-selector-modal').style.display = 'flex';
}

function closeExerciseSelectorModal() {
    document.getElementById('exercise-selector-modal').style.display = 'none';
}

function applySelectedExercises() {
    const rows = document.querySelectorAll('.workout-exercise-row');
    const collected = [];

    rows.forEach(function(row) {
        const checkbox = row.querySelector('.workout-ex-checkbox');
        if (!checkbox || !checkbox.checked) {
            return;
        }

        const idEx = Number(checkbox.value);
        const name = checkbox.dataset.name || ('Exercise #' + idEx);
        const typeEx = (checkbox.dataset.type || '').toLowerCase();
        const isCardio = typeEx === 'cardio';
        const setsInput = row.querySelector('.workout-ex-sets');
        const repsInput = row.querySelector('.workout-ex-reps');
        const weightInput = row.querySelector('.workout-ex-weight');
        const timeInput = row.querySelector('.workout-ex-time');

        const sets = isCardio ? 0 : Math.max(1, Number(setsInput.value || 1));
        const reps = isCardio ? 0 : Math.max(1, Number(repsInput.value || 1));
        const weight = isCardio ? 0 : Math.max(0, Number(weightInput.value || 0));
        const time = isCardio ? Math.max(1, Number(timeInput.value || 1)) : 0;

        collected.push({
            id_ex: idEx,
            name: name,
            type_ex: typeEx,
            sets: sets,
            reps: reps,
            weight: weight,
            time: time
        });
    });

    selectedWorkoutExercises = collected;
    updateSelectedExercisesInput();
    renderSelectedExercisesSummary();

    closeExerciseSelectorModal();
}

function toggleExerciseConfig(checkbox) {
    const row = checkbox.closest('.workout-exercise-row');
    if (!row) {
        return;
    }

    const isCardio = (checkbox.dataset.type || '').toLowerCase() === 'cardio';
    const setsInput = row.querySelector('.workout-ex-sets');
    const repsInput = row.querySelector('.workout-ex-reps');
    const weightInput = row.querySelector('.workout-ex-weight');
    const timeInput = row.querySelector('.workout-ex-time');

    if (!checkbox.checked) {
        row.querySelectorAll('.workout-ex-config').forEach(function(input) {
            input.disabled = true;
        });
        return;
    }

    setsInput.disabled = isCardio;
    repsInput.disabled = isCardio;
    weightInput.disabled = isCardio;
    timeInput.disabled = !isCardio;
}

function resetWorkoutExerciseSelection() {
    selectedWorkoutExercises = [];
    document.getElementById('selected_exercises').value = '';
    document.getElementById('selected-exercises-summary').innerHTML = 'No exercises selected';

    document.querySelectorAll('.workout-exercise-row').forEach(function(row) {
        const checkbox = row.querySelector('.workout-ex-checkbox');
        const setsInput = row.querySelector('.workout-ex-sets');
        const repsInput = row.querySelector('.workout-ex-reps');
        const weightInput = row.querySelector('.workout-ex-weight');
        const timeInput = row.querySelector('.workout-ex-time');

        checkbox.checked = false;
        setsInput.value = 3;
        repsInput.value = 10;
        weightInput.value = 0;
        timeInput.value = 30;

        row.querySelectorAll('.workout-ex-config').forEach(function(input) {
            input.disabled = true;
        });
    });

    const workoutCategorySelect = document.getElementById('work_id_cat');
    if (workoutCategorySelect) {
        workoutCategorySelect.value = '';
    }

    const newCategoryInput = document.getElementById('new_work_categorie');
    if (newCategoryInput) {
        newCategoryInput.value = '';
    }
}


function fillEditForm(id, name, type, muscle, cal, fatigue, description) {
    document.getElementById('form-action').value = 'update';
    document.getElementById('edit-id').value = id;
    document.getElementById('ex_name').value = name;
    document.getElementById('ex_type').value = type;
    document.getElementById('ex_calories').value = cal;
    document.getElementById('ex_fatigue').value = fatigue;
    document.getElementById('ex_description').value = description;

    // handle multiple muscles
    // handle multiple muscles
    const muscles = muscle.split(',');
    document.querySelectorAll('input[name="ex_target_muscle[]"]').forEach(cb => {
        cb.checked = muscles.includes(cb.value);
    });

    document.getElementById('exercise-form').scrollIntoView({ behavior: 'smooth' });
}
</script>






<body>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="loader-track">
            <div class="preloader-wrapper">
                <div class="spinner-layer spinner-blue">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
                <div class="spinner-layer spinner-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>

                <div class="spinner-layer spinner-yellow">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>

                <div class="spinner-layer spinner-green">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div><!-- Navbar*********************************************** -->
        <div class="pcoded-container navbar-wrapper"><!-- Navbar**************************************** -->
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">
                        <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
                            <i class="ti-menu"></i>
                        </a>
                        <div class="mobile-search waves-effect waves-light">
                            <div class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-prepend search-close"><i class="ti-close input-group-text"></i></span>
                                        <input type="text" class="form-control" placeholder="Enter Keyword">
                                        <span class="input-group-append search-btn"><i class="ti-search input-group-text"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="index.html">
                            <img class="img-fluid" src="assets/images/logo.png" alt="Theme-Logo" />
                        </a>
                        <a class="mobile-options waves-effect waves-light">
                            <i class="ti-more"></i>
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <ul class="nav-left">
                            <li>
                                <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div>
                            </li>
                            <li>
                                <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
                                    <i class="ti-fullscreen"></i>
                                </a>
                            </li>
                        </ul>
                        <ul class="nav-right">
                            <li class="header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <i class="ti-bell"></i>
                                    <span class="badge bg-c-red"></span>
                                </a>
                                <ul class="show-notification">
                                    <li>
                                        <h6>Notifications</h6>
                                        <label class="label label-danger">New</label>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-2.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">John Doe</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-4.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">Joseph William</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <div class="media">
                                            <img class="d-flex align-self-center img-radius" src="assets/images/avatar-3.jpg" alt="Generic placeholder image">
                                            <div class="media-body">
                                                <h5 class="notification-user">Sara Soudein</h5>
                                                <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                                <span class="notification-time">30 minutes ago</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <li class="user-profile header-notification">
                                <a href="#!" class="waves-effect waves-light">
                                    <img src="assets/images/avatar-4.jpg" class="img-radius" alt="User-Profile-Image">
                                    <span>John Doe</span>
                                    <i class="ti-angle-down"></i>
                                </a>
                                <ul class="show-notification profile-notification">
                                    <li class="waves-effect waves-light">
                                        <a href="#!">
                                            <i class="ti-settings"></i> Settings
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="user-profile.html">
                                            <i class="ti-user"></i> Profile
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="email-inbox.html">
                                            <i class="ti-email"></i> My Messages
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="auth-lock-screen.html">
                                            <i class="ti-lock"></i> Lock Screen
                                        </a>
                                    </li>
                                    <li class="waves-effect waves-light">
                                        <a href="auth-normal-sign-in.html">
                                            <i class="ti-layout-sidebar-left"></i> Logout
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="pcoded-main-container">
                <div class="pcoded-wrapper"><!-- Navbar************************************************** -->
                    <nav class="pcoded-navbar"><!-- Navbar*********************************************** -->
                        <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
                        <div class="pcoded-inner-navbar main-menu">
                            <div class="">
                                <div class="main-menu-header">
                                    <img class="img-80 img-radius" src="assets/images/avatar-4.jpg" alt="User-Profile-Image">
                                    <div class="user-details">
                                        <span id="more-details">John Doe<i class="fa fa-caret-down"></i></span>
                                    </div>
                                </div>
                                <div class="main-menu-content">
                                    <ul>
                                        <li class="more-details">
                                            <a href="user-profile.html"><i class="ti-user"></i>View Profile</a>
                                            <a href="#!"><i class="ti-settings"></i>Settings</a>
                                            <a href="auth-normal-sign-in.html"><i class="ti-layout-sidebar-left"></i>Logout</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="p-15 p-b-0">
                                <form class="form-material">
                                    <div class="form-group form-primary">
                                        <input type="text" name="footer-email" class="form-control">
                                        <span class="form-bar"></span>
                                        <label class="float-label"><i class="fa fa-search m-r-10"></i>Search Friend</label>
                                    </div>
                                </form>
                            </div>
                            <div class="pcoded-navigation-label">Navigation</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="index.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                        <span class="pcoded-mtext">Dashboard</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">UI Element</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layout-grid2-alt"></i><b>BC</b></span>
                                        <span class="pcoded-mtext">Basic</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class=" ">
                                            <a href="breadcrumb.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Breadcrumbs</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="button.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Button</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="accordion.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Accordion</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="tabs.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Tabs</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="color.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Color</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="label-badge.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Label Badge</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="tooltip.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Tooltip And Popover</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="typography.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Typography</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class=" ">
                                            <a href="notification.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Notifications</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Forms</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="active">
                                    <a href="form-elements-component.php" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                        <span class="pcoded-mtext">Form</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Tables</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="bs-basic-table.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-receipt"></i><b>B</b></span>
                                        <span class="pcoded-mtext">Table</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Chart And Maps</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="">
                                    <a href="chart-morris.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-bar-chart-alt"></i><b>C</b></span>
                                        <span class="pcoded-mtext">Charts</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="map-google.html" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-map-alt"></i><b>M</b></span>
                                        <span class="pcoded-mtext">Maps</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            </ul>
                            <div class="pcoded-navigation-label">Pages</div>
                            <ul class="pcoded-item pcoded-left-item">
                                <li class="pcoded-hasmenu ">
                                    <a href="javascript:void(0)" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-id-badge"></i><b>A</b></span>
                                        <span class="pcoded-mtext">Pages</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                    <ul class="pcoded-submenu">
                                        <li class="">
                                            <a href="auth-normal-sign-in.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Login</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="auth-sign-up.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                <span class="pcoded-mtext">Registration</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="sample-page.html" class="waves-effect waves-dark">
                                                <span class="pcoded-micon"><i class="ti-layout-sidebar-left"></i><b>S</b></span>
                                                <span class="pcoded-mtext">Sample Page</span>
                                                <span class="pcoded-mcaret"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <div class="pcoded-content">
                        <!-- FOOVIA Sport Dashboard Start -->
                        <div class="pcoded-inner-content">
                            <!-- Main-body start -->
                            <div class="main-body">
                                <div class="page-header">
                                    <h1 class="page-title">
                                        <i class="ti-heart"></i>
                                        Sport & Training
                                    </h1>
                                </div>

                                <!-- HORIZONTAL DASHBOARD NAVIGATION -->
                                <div class="dashboard-nav" style="display: flex; gap: 20px; padding: 20px; background: #f5f5f5; border-radius: 5px; margin-bottom: 20px;">
                                    <button class="dashboard-item" data-section="workout" style="flex: 1; padding: 20px; background: white; border: 2px solid #ddd; border-radius: 5px; cursor: pointer; transition: all 0.3s; font-size: 16px; font-weight: 500;">
                                        <i  href="#workout" class="ti-list"></i> Workouts
                                    </button>
                                    <button class="dashboard-item active" data-section="exercise" style="flex: 1; padding: 20px; background: #4099ff; color: white; border: 2px solid #4099ff; border-radius: 5px; cursor: pointer; transition: all 0.3s; font-size: 16px; font-weight: 500;">
                                        <i class="ti-pulse"></i> Exercises
                                    </button>
                                </div>

                                <!-- WORKOUT SECTION *****************************************************-->
                                <div id="workout-section" class="section-content" style="display: none;">
                                    <div style="display: flex; gap: 20px; min-height: 600px; flex-wrap: nowrap; align-items: flex-start;">
                                        <!-- LEFT SIDE: WORKOUTS LIST -->
                                        <div style="flex: 1 1 auto; min-width: 0; max-width: calc(100% - 520px); background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-y: auto;">
                                            <div style="font-size: 18px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                                <i class="ti-list"></i>
                                                Available Workouts
                                            </div>

                                            <!-- FILTER BY CATEGORY -->
                                            <div style="margin-bottom: 20px;">
                                                <label style="font-weight: 600; margin-bottom: 10px; display: block; font-size: 14px;">Filter by Category:</label>
                                                <select id="backoffice-workout-category-filter" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                    <option value="">All Categories</option>
                                                    <?php foreach ($workCategories as $cat): ?>
                                                        <option value="<?= htmlspecialchars(strtolower(trim($cat['name_cat'])), ENT_QUOTES) ?>"><?= htmlspecialchars($cat['name_cat']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- SEARCH -->
                                            <div style="margin-bottom: 14px; display:flex; gap:8px; align-items:center;">
                                                <input id="backoffice-workout-search" type="search" placeholder="Search workouts by name, category, or duration" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px;" />
                                                <button id="backoffice-workout-clear" type="button" style="padding:8px 12px; border:1px solid #ddd; background:#f5f5f5; border-radius:4px; cursor:pointer;">Clear</button>
                                            </div>

                                            <div id="workouts-list-container" style="display: flex; flex-direction: column; gap: 12px;">

    <?php if (empty($workouts)): ?>
        <div style="text-align: center; padding: 40px 20px; color: #999;">
            <div style="font-size: 48px; margin-bottom: 10px;">
                <i class="ti-package"></i>
            </div>
            <div style="font-weight: 600; margin-bottom: 5px;">No Workouts Yet</div>
            <div style="font-size: 14px;">Add your first workout using the form on the right ==> </div>
        </div>
    <?php else: ?>
        <?php foreach ($workouts as $wk): ?>
            <div id="card-<?= $wk['id_work'] ?>" class="workout-card" style="background: white; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
                
                <!-- Image -->
                <?php if (!empty($wk['pic_work'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($wk['pic_work']) ?>" 
                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                    <i class="ti-image" style="color: #aaa; font-size: 24px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;"></i>
                <?php endif; ?>

                <!-- Info -->
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;">
                        <?= htmlspecialchars($wk['name_work'] . ' (id=' . $wk['id_work'] . ')') ?>
                    </div>
                    <div style="font-size: 12px; color: #666; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #e8f0fe; color: #4099ff; padding: 2px 8px; border-radius: 20px;">
                            ⏱️ <?= (int)$wk['duree_work'] ?> min
                        </span>
                        <span style="background: #fff3cd; color: #d97706; padding: 2px 8px; border-radius: 20px;">
                            🔥 <?= (int)$wk['cal_work'] ?> cal
                        </span>
                        <?php if (!empty($wk['name_cat'])): ?>
                            <span style="background: #e8f7ee; color: #218838; padding: 2px 8px; border-radius: 20px;">
                                🗂️ <?= htmlspecialchars($wk['name_cat']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Delete button & edit button -->
                <form method="POST" action="../../controle/SPORT_MOULE/controle_workout.php" style="margin: 0; display: flex; gap: 5px;">
                    <input type="hidden" name="delete_id" value="<?= (int)$wk['id_work'] ?>">
                    
                    <!-- Delete button -->
                    <button type="submit" name="action" value="delete"
                        style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 16px; padding: 5px;"
                        onclick="return confirm('Delete this workout?')">
                        <i class="ti-trash"></i>
                    </button>

                    <!-- Edit button -->
                    <button type="button"
                        onclick="fillEditWorkoutForm(<?= (int)$wk['id_work'] ?>, '<?= addslashes($wk['name_work']) ?>', <?= (int)$wk['duree_work'] ?>, <?= isset($wk['id_cat']) ? (int)$wk['id_cat'] : 0 ?>, '<?= htmlspecialchars(json_encode($workoutExercisesByWorkout[(int)$wk['id_work']] ?? []), ENT_QUOTES, 'UTF-8') ?>')"
                        style="background: none; border: none; color: #4099ff; cursor: pointer; font-size: 16px; padding: 5px;">
                        <i class="ti-pencil"></i>
                    </button>
                </form>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>

                            </div>
                        </div>

                        <!-- RIGHT SIDE: WORKOUT FORM -->
                        <div style="flex: 0 0 480px; max-width: 480px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-y: auto;">
                            
                            <form onsubmit="return validateWorkoutForm()" class="workout-form" id="workout-form" action="../../controle/SPORT_MOULE/controle_workout.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">

                                <input type="hidden" name="action" id="work-form-action" value="add">
                                <input type="hidden" name="edit_id" id="work-edit-id" value="">
                                <input type="hidden" name="id_user" value="1">

                                <div style="font-size: 18px; font-weight: 600; display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <i class="ti-pencil-alt"></i>
                                    Add New Workout
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Workout Name</label>
                                    <input type="text" id="work_name" name="work_name" class="form-input" placeholder="e.g., Full Body Strength" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Duration (minutes): <span id="work_duree_value">45</span></label>
                                    <input type="range" id="work_duree" name="work_duree" class="form-input" min="15" max="180" value="45" step="1" oninput="document.getElementById('work_duree_value').textContent = this.value" style="padding: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div style="display: flex; flex-direction: column;">
                                        <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Workout Category</label>
                                        <select id="work_id_cat" name="id_cat" class="form-input" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                            <option value="">Choose a category</option>
                                            <?php foreach ($workCategories as $cat): ?>
                                                <option value="<?= (int)$cat['id_cat'] ?>" <?= $selectedCategoryFromQuery === (int)$cat['id_cat'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name_cat']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div style="display: flex; flex-direction: column;">
                                        <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Or Create Category</label>
                                        <input type="text" id="new_work_categorie" name="new_work_categorie" class="form-input" placeholder="e.g., Fat Loss" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                        <button type="button" onclick="submitNewWorkoutCategory()" style="margin-top: 8px; padding: 9px 12px; background: #17a2b8; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">
                                            <i class="ti-plus"></i> Add Category
                                        </button>
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-weight: 600; margin-bottom: 0; font-size: 14px;">Category List</label>
                                    <div style="max-height: 140px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; background: #fafafa; padding: 8px;">
                                        <?php if (empty($workCategories)): ?>
                                            <div style="font-size: 13px; color: #888;">No categories available.</div>
                                        <?php else: ?>
                                            <?php foreach ($workCategories as $cat): ?>
                                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 6px 4px; border-bottom: 1px solid #eee;">
                                                    <span style="font-size: 13px; color: #444;"><?= htmlspecialchars($cat['name_cat']) ?></span>
                                                    <button type="button" onclick="deleteWorkoutCategory(<?= (int)$cat['id_cat'] ?>, '<?= addslashes($cat['name_cat']) ?>')" style="padding: 4px 8px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                                                        <i class="ti-trash"></i> Delete
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Calories Burned</label>
                                    <div style="padding: 10px; border: 1px dashed #ddd; border-radius: 4px; font-size: 13px; color: #666; background: #fafafa;">
                                        Calculated automatically from selected exercises.
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <label style="font-weight: 600; margin-bottom: 0; font-size: 14px;">Exercises In This Workout</label>
                                    <input type="hidden" id="selected_exercises" name="selected_exercises" value="">
                                    <button type="button" onclick="openExerciseSelectorModal()" style="padding: 10px 12px; background: #f7faff; border: 1px solid #4099ff; color: #4099ff; border-radius: 4px; font-weight: 600; cursor: pointer; text-align: left;">
                                        <i class="ti-list"></i> Select Exercises
                                    </button>
                                    <div id="selected-exercises-summary" style="font-size: 13px; color: #666; background: #fafafa; border: 1px dashed #ddd; border-radius: 4px; padding: 10px; min-height: 42px; max-height: 130px; overflow-y: auto;">
                                        No exercises selected
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Workout Image</label>
                                    <input type="file" id="work_picture" name="work_picture" class="form-input" accept="image/*" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                </div>

                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                    <button type="submit" style="flex: 1; padding: 12px; background: #4099ff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.3s;">
                                        <i class="ti-save"></i> Add / Modify Workout
                                    </button>
                                    <button type="reset" style="flex: 1; padding: 12px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s;">
                                        <i class="ti-close"></i> Clear
                                    </button>
                                </div>
                            </form>

                            <form id="standalone-category-form" action="../../controle/SPORT_MOULE/controle_workout.php" method="POST" style="display: none;">
                                <input type="hidden" name="action" value="add_category">
                                <input type="hidden" id="standalone_new_work_categorie" name="new_work_categorie" value="">
                            </form>

                            <form id="standalone-delete-category-form" action="../../controle/SPORT_MOULE/controle_workout.php" method="POST" style="display: none;">
                                <input type="hidden" name="action" value="delete_category">
                                <input type="hidden" id="standalone_delete_id_cat" name="delete_id_cat" value="">
                            </form>

                        </div>
                    </div>

                    <div id="exercise-selector-modal" style="display: none; position: fixed; z-index: 1050; inset: 0; background: rgba(0,0,0,0.45); align-items: center; justify-content: center; padding: 20px;">
                        <div style="background: white; border-radius: 8px; width: min(900px, 100%); max-height: 85vh; display: flex; flex-direction: column;">
                            <div style="padding: 16px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; font-size: 18px; font-weight: 600;">Select Exercises For Workout</h4>
                                <button type="button" onclick="closeExerciseSelectorModal()" style="background: none; border: none; font-size: 22px; line-height: 1; cursor: pointer; color: #666;">&times;</button>
                            </div>

                            <div style="padding: 12px 20px; font-size: 13px; color: #666; border-bottom: 1px solid #eee;">
                                For cardio exercises, set only time. For non-cardio exercises, set sets, reps, and weight.
                            </div>

                            <div style="padding: 12px 20px; overflow-y: auto; flex: 1;">
                                <?php if (empty($exercises)): ?>
                                    <div style="text-align: center; padding: 24px; color: #999;">No exercises available.</div>
                                <?php else: ?>
                                    <?php foreach ($exercises as $ex): ?>
                                        <div class="workout-exercise-row" style="display: grid; grid-template-columns: 1.8fr 0.6fr 0.6fr 0.6fr 0.6fr; gap: 10px; align-items: center; padding: 10px; border: 1px solid #eee; border-radius: 6px; margin-bottom: 8px;">
                                            <label style="display: flex; align-items: center; gap: 8px; margin: 0; font-weight: 600; cursor: pointer;">
                                                <input type="checkbox" class="workout-ex-checkbox" value="<?= (int)$ex['id_ex'] ?>" data-name="<?= htmlspecialchars($ex['name_ex'], ENT_QUOTES) ?>" data-type="<?= htmlspecialchars(strtolower((string)$ex['type_ex']), ENT_QUOTES) ?>" onchange="toggleExerciseConfig(this)">
                                                <span><?= htmlspecialchars($ex['name_ex']) ?> (<?= htmlspecialchars($ex['type_ex']) ?>)</span>
                                            </label>
                                            <input type="number" class="workout-ex-sets workout-ex-config" min="1" value="3" disabled style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" title="sets" placeholder="sets">
                                            <input type="number" class="workout-ex-reps workout-ex-config" min="1" value="10" disabled style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" title="reps" placeholder="reps">
                                            <input type="number" class="workout-ex-weight workout-ex-config" min="0" value="0" step="0.5" disabled style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" title="weight" placeholder="weight">
                                            <input type="number" class="workout-ex-time workout-ex-config" min="0" value="30" disabled style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;" title="time" placeholder="time (min)">
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div style="padding: 14px 20px; border-top: 1px solid #eee; display: flex; justify-content: flex-end; gap: 10px;">
                                <button type="button" onclick="closeExerciseSelectorModal()" style="padding: 10px 14px; border: 1px solid #ddd; background: #f8f8f8; border-radius: 4px; cursor: pointer;">Cancel</button>
                                <button type="button" onclick="applySelectedExercises()" style="padding: 10px 14px; border: none; background: #4099ff; color: white; border-radius: 4px; cursor: pointer;">Apply Selection</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- WORKOUT SECTION *****************************************************-->
                                <!-- EXERCISES SECTION -->
                                <div id="exercise-section" class="section-content" style="display: block;">
                                    <div style="display: flex; gap: 20px; min-height: 600px; flex-wrap: nowrap; align-items: flex-start;">

                                        <!-- LEFT SIDE: EXERCISE FORM -->
                                        <div style="flex: 0 0 480px; max-width: 480px; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-y: auto;">
                                            <form onsubmit="return validateForm()" class="exercise-form" id="exercise-form" action="../../controle/SPORT_MOULE/controle_exercice.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">

                                                <input type="hidden" name="action" id="form-action" value="add">
                                                <input type="hidden" name="edit_id" id="edit-id" value="">

                                                <div style="font-size: 18px; font-weight: 600; display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                                    <i class="ti-pencil-alt"></i>
                                                    Add New Exercise
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Exercise Name</label>
                                                    <input type="text" id="ex_name" name="ex_name" class="form-input" placeholder="e.g., Barbell Squat" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Type</label>
                                                    <select name="ex_type" id="ex_type" class="form-select" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        <option value="">Select Type</option>
                                                        <option value="compound">Compound</option>
                                                        <option value="isolation">Isolation</option>
                                                        <option value="cardio">Cardio</option>
                                                    </select>
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Target Muscle</label>
                                                    <div id="ex_target_muscle" style="display: flex; flex-wrap: wrap; gap: 8px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                                                        <?php foreach(['calves','hamstrings','quadriceps','adductors','glutes','abs','obliques','lower_back','lats','traps','chest','delts','biceps','triceps','forearms','neck'] as $m): ?>
                                                            <label style="display: flex; align-items: center; gap: 4px; font-size: 13px; cursor: pointer;">
                                                                <input type="checkbox" name="ex_target_muscle[]" value="<?= $m ?>"> <?= ucfirst($m) ?>
                                                            </label>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Description</label>
                                                    <textarea name="ex_description" id="ex_description" class="form-textarea" placeholder="Describe the exercise..." style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; resize: vertical; min-height: 80px;"></textarea>
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Picture or GIF</label>
                                                    <input name="ex_picture" id="ex_picture" type="file" class="form-input" placeholder="Upload exercise GIF or image" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Calories Per Rep</label>
                                                    <input name="ex_calories" id="ex_calories" type="text" class="form-input" placeholder="5" step="0.1" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                </div>

                                                <div style="display: flex; flex-direction: column;">
                                                    <label style="font-weight: 600; margin-bottom: 8px; font-size: 14px;">Fatigue Ratio</label>
                                                    <input name="ex_fatigue" id="ex_fatigue" type="text" class="form-input" placeholder="0.8" step="0.01" style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                </div>

                                                <div style="display: flex; gap: 10px; margin-top: 15px;">
                                                    <button type="submit" style="flex: 1; padding: 12px; background: #4099ff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.3s;">
                                                        <i class="ti-save"></i> Add / Modify Exercise
                                                    </button>
                                                    <button type="reset" style="flex: 1; padding: 12px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.3s;">
                                                        <i class="ti-close"></i> Clear
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- RIGHT SIDE: EXERCISES LIST -->
                                        <div style="flex: 1 1 auto; min-width: 0; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow-y: auto;">
                                            <div style="font-size: 18px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                                                <i class="ti-pulse"></i>
                                                Available Exercises
                                            </div>

                                            <!-- FILTER BY MUSCLES -->
                                            <div style="margin-bottom: 20px;">
                                                <label style="font-weight: 600; margin-bottom: 10px; display: block; font-size: 14px;">Filter by Muscle:</label>
                                                <select id="muscle-filter" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                    <option value="">All Muscles</option>
                                                    <option value="calves">Calves</option>
                                                    <option value="hamstrings">Hamstrings</option>
                                                    <option value="quadriceps">Quadriceps</option>
                                                    <option value="adductors">Adductors</option>
                                                    <option value="glutes">Glutes</option>
                                                    <option value="abs">Abs</option>
                                                    <option value="obliques">Obliques</option>
                                                    <option value="lower_back">Lower Back</option>
                                                    <option value="lats">Lats</option>
                                                    <option value="traps">Traps</option>
                                                    <option value="chest">Chest</option>
                                                    <option value="delts">Delts</option>
                                                    <option value="biceps">Biceps</option>
                                                    <option value="triceps">Triceps</option>
                                                    <option value="forearms">Forearms</option>
                                                    <option value="neck">Neck</option>
                                                </select>
                                            </div>

                                            <!-- SEARCH -->
                                            <div style="margin-bottom: 14px; display:flex; gap:8px; align-items:center;">
                                                <input id="backoffice-exercise-search" type="search" placeholder="Search exercises by name, type, or muscle" style="flex:1; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px;" />
                                                <button id="backoffice-exercise-clear" type="button" style="padding:8px 12px; border:1px solid #ddd; background:#f5f5f5; border-radius:4px; cursor:pointer;">Clear</button>
                                            </div>

                                            <div id="exercises-list-container" style="display: flex; flex-direction: column; gap: 12px;">
    <?php if (empty($exercises)): ?>
        <div style="text-align: center; padding: 40px 20px; color: #999;">
            <div style="font-size: 48px; margin-bottom: 10px;"><i class="ti-package"></i></div>
            <div style="font-weight: 600; margin-bottom: 5px;">No Exercises Yet</div>
            <div style="font-size: 14px;">Add your first exercise using the form on the left.</div>
        </div>
    <?php else: ?>
        <?php foreach ($exercises as $ex): ?>
            <div id="card-<?= $ex['id_ex'] ?>" class="exercise-card" style="background: white; border-radius: 6px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 15px;">
                <?php if (!empty($ex['gif_ex'])): ?>
                    <img src="data:image/gif;base64,<?= base64_encode($ex['gif_ex']) ?>" style="width: 10%; height: 10%; object-fit: cover;">
                <?php else: ?>
                    <i class="ti-image" style="color: #aaa; font-size: 24px;"></i>
                <?php endif; ?>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; font-size: 15px; margin-bottom: 4px;">
                        <?= htmlspecialchars($ex['name_ex'] . ' (id=' . $ex['id_ex'] . ')') ?>
                    </div>
                    <div style="font-size: 12px; color: #666; display: flex; gap: 8px; flex-wrap: wrap;">
                        <span style="background: #e8f0fe; color: #4099ff; padding: 2px 8px; border-radius: 20px;">
                            <?= htmlspecialchars($ex['type_ex']) ?>
                        </span>
                        <div style="font-size: 12px; color: #666; display: flex; gap: 8px; flex-wrap: wrap;">
                            <span style="background: #e8f0fe; color: #4099ff; padding: 2px 8px; border-radius: 20px;">
                                <?= htmlspecialchars($ex['type_ex']) ?>
                            </span>
                            <?php foreach(explode(',', $ex['muscle_ex']) as $m): ?>
                                <span style="background: #e8f7ee; color: #28a745; padding: 2px 8px; border-radius: 20px;">
                                    <?= htmlspecialchars(trim($m)) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <span style="color: #999;">&#128293; <?= (int)$ex['cal_ex'] ?> cal</span>
                        <span style="background: #e8f7ee; color: red; padding: 2px 8px; border-radius: 20px;">
                            <?= htmlspecialchars($ex['fatigue_ex']/10) ?>
                        </span>
                    </div>
                </div>
                <form method="POST" action="../../controle/SPORT_MOULE/controle_exercice.php" style="margin: 0; display: flex; gap: 5px;">
                    <input type="hidden" name="delete_id" value="<?= (int)$ex['id_ex'] ?>">
                    <button type="submit" name="action" value="delete"
                        style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 16px; padding: 5px;"
                        onclick="return confirm('Delete this exercise?')">
                        <i class="ti-trash"></i>
                    </button>
                    <button type="button"
                        onclick="fillEditForm(<?= $ex['id_ex'] ?>, '<?= addslashes($ex['name_ex']) ?>', '<?= addslashes($ex['type_ex']) ?>', '<?= addslashes($ex['muscle_ex']) ?>', <?= (int)$ex['cal_ex'] ?>, <?= (float)$ex['fatigue_ex'] ?>, '<?= addslashes($ex['description_ex']) ?>')"
                        style="background: none; border: none; color: #4099ff; cursor: pointer; font-size: 16px; padding: 5px;">
                        <i class="ti-pencil"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
                                            </div>
                                            <div id="backoffice-exercise-empty" style="display:none; text-align:center; padding:18px; color:#999;">No exercises match your search.</div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- Main-body end -->
                            <div id="styleSelector">

                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
    </div>


    <!-- Warning Section Starts -->
    <!-- Older IE warning message -->
    <!--[if lt IE 10]>
<div class="ie-warning">
    <h1>Warning!!</h1>
    <p>You are using an outdated version of Internet Explorer, please upgrade <br/>to any of the following web browsers
        to access this website.</p>
    <div class="iew-container">
        <ul class="iew-download">
            <li>
                <a href="http://www.google.com/chrome/">
                    <img src="assets/images/browser/chrome.png" alt="Chrome">
                    <div>Chrome</div>
                </a>
            </li>
            <li>
                <a href="https://www.mozilla.org/en-US/firefox/new/">
                    <img src="assets/images/browser/firefox.png" alt="Firefox">
                    <div>Firefox</div>
                </a>
            </li>
            <li>
                <a href="http://www.opera.com">
                    <img src="assets/images/browser/opera.png" alt="Opera">
                    <div>Opera</div>
                </a>
            </li>
            <li>
                <a href="https://www.apple.com/safari/">
                    <img src="assets/images/browser/safari.png" alt="Safari">
                    <div>Safari</div>
                </a>
            </li>
            <li>
                <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                    <img src="assets/images/browser/ie.png" alt="">
                    <div>IE (9 & above)</div>
                </a>
            </li>
        </ul>
    </div>
    <p>Sorry for the inconvenience!</p>
</div>
<![endif]-->
    <!-- Warning Section Ends -->
    <!-- Required Jquery -->
    <script type="text/javascript" src="assets/js/jquery/jquery.min.js "></script>
    <script type="text/javascript" src="assets/js/jquery-ui/jquery-ui.min.js "></script>
    <script type="text/javascript" src="assets/js/popper.js/popper.min.js"></script>
    <script type="text/javascript" src="assets/js/bootstrap/js/bootstrap.min.js "></script>
    <!-- waves js -->
    <script src="assets/pages/waves/js/waves.min.js"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="assets/js/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Custom js -->
    <script src="assets/js/pcoded.min.js"></script>
    <script src="assets/js/vertical/vertical-layout.min.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script type="text/javascript" src="assets/js/script.js"></script>
    <script>
        (function() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('added') === '1') {
                alert('Exercise added successfully.');
                params.delete('added');
            }

            if (params.get('category_added') === '1') {
                alert('Category added successfully.');
                params.delete('category_added');
            }

            if (params.get('category_error')) {
                alert('Category error: ' + params.get('category_error'));
                params.delete('category_error');
            }

            if (params.get('category_deleted') === '1') {
                alert('Category deleted successfully.');
                params.delete('category_deleted');
            }

            if (params.get('category_delete_error')) {
                alert('Category delete error: ' + params.get('category_delete_error'));
                params.delete('category_delete_error');
            }

            if (params.toString() !== window.location.search.replace(/^\?/, '')) {
                const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                window.history.replaceState({}, document.title, newUrl);
            }
        })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navButtons = document.querySelectorAll('.dashboard-item[data-section]');
            const sections = {
                workout: document.getElementById('workout-section'),
                exercise: document.getElementById('exercise-section')
            };
            const workoutForm = document.getElementById('workout-form');
            const selectorModal = document.getElementById('exercise-selector-modal');

            function setActiveSection(sectionKey) {
                Object.keys(sections).forEach(function(key) {
                    if (sections[key]) {
                        sections[key].style.display = key === sectionKey ? 'block' : 'none';
                    }
                });

                navButtons.forEach(function(button) {
                    const isActive = button.dataset.section === sectionKey;
                    button.classList.toggle('active', isActive);
                    button.style.background = isActive ? '#4099ff' : 'white';
                    button.style.color = isActive ? 'white' : '#333';
                    button.style.borderColor = isActive ? '#4099ff' : '#ddd';
                });
            }

            navButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const targetSection = button.dataset.section;
                    if (sections[targetSection]) {
                        setActiveSection(targetSection);
                    }
                });
            });

            const currentParams = new URLSearchParams(window.location.search);
            const forceWorkoutSection = currentParams.get('section') === 'workout' || currentParams.get('category_id') || currentParams.get('category_added') || currentParams.get('category_error') || currentParams.get('category_deleted') || currentParams.get('category_delete_error');
            const initiallyActive = document.querySelector('.dashboard-item.active[data-section]');
            if (forceWorkoutSection) {
                setActiveSection('workout');
            } else {
                setActiveSection(initiallyActive ? initiallyActive.dataset.section : 'exercise');
            }

            if (workoutForm) {
                workoutForm.addEventListener('reset', function() {
                    setTimeout(function() {
                        document.getElementById('work-form-action').value = 'add';
                        document.getElementById('work-edit-id').value = '';
                        resetWorkoutExerciseSelection();
                    }, 0);
                });
            }

            if (selectorModal) {
                selectorModal.addEventListener('click', function(e) {
                    if (e.target === selectorModal) {
                        closeExerciseSelectorModal();
                    }
                });
            }

            // Backoffice exercise search filter
            (function() {
                const searchInput = document.getElementById('backoffice-exercise-search');
                const clearBtn = document.getElementById('backoffice-exercise-clear');
                const listContainer = document.getElementById('exercises-list-container');
                if (!listContainer) return;

                const rows = Array.from(listContainer.querySelectorAll('.exercise-card'));

                const normalize = (text) => String(text || '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();

                rows.forEach((r) => {
                    const name = r.querySelector('div[style*="font-weight: 600"]')?.textContent || '';
                    const meta = r.querySelector('div[style*="font-size: 12px"]')?.textContent || '';
                    r.dataset.search = normalize([name, meta].join(' '));
                });

                const applyFilter = () => {
                    const q = normalize(searchInput?.value || '');
                    let anyVisible = false;
                    rows.forEach((r) => {
                        const match = q === '' || (r.dataset.search || '').includes(q);
                        r.style.display = match ? 'flex' : 'none';
                        if (match) anyVisible = true;
                    });
                    // show empty placeholder if none
                    const emptyPlaceholder = document.getElementById('backoffice-exercise-empty');
                    if (emptyPlaceholder) {
                        emptyPlaceholder.style.display = anyVisible ? 'none' : '';
                    }
                };

                if (searchInput) {
                    searchInput.addEventListener('input', applyFilter);
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        if (searchInput) searchInput.value = '';
                        applyFilter();
                        searchInput?.focus();
                    });
                }
            })();

            // Backoffice workout search filter
            (function() {
                const searchInput = document.getElementById('backoffice-workout-search');
                const categoryFilter = document.getElementById('backoffice-workout-category-filter');
                const clearBtn = document.getElementById('backoffice-workout-clear');
                const listContainer = document.getElementById('workouts-list-container');
                if (!listContainer) return;

                const rows = Array.from(listContainer.querySelectorAll('.workout-card'));

                const normalize = (text) => String(text || '').toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();

                rows.forEach((r) => {
                    const name = r.querySelector('div[style*="font-weight: 600"]')?.textContent || '';
                    const meta = r.querySelector('div[style*="font-size: 12px"]')?.textContent || '';
                    const categorySpan = r.querySelector('span[style*="color: #218838"]')?.textContent || '';
                    r.dataset.search = normalize([name, meta, categorySpan].join(' '));
                    r.dataset.category = normalize(categorySpan);
                });

                const applyFilter = () => {
                    const q = normalize(searchInput?.value || '');
                    const selectedCategory = normalize(categoryFilter?.value || '');
                    let anyVisible = false;
                    rows.forEach((r) => {
                        const matchSearch = q === '' || (r.dataset.search || '').includes(q);
                        const matchCategory = selectedCategory === '' || (r.dataset.category || '').includes(selectedCategory);
                        const match = matchSearch && matchCategory;
                        r.style.display = match ? 'flex' : 'none';
                        if (match) anyVisible = true;
                    });
                    // show empty placeholder if none
                    const emptyPlaceholder = document.getElementById('backoffice-workout-empty');
                    if (emptyPlaceholder) {
                        emptyPlaceholder.style.display = anyVisible ? 'none' : '';
                    }
                };

                if (searchInput) {
                    searchInput.addEventListener('input', applyFilter);
                }

                if (categoryFilter) {
                    categoryFilter.addEventListener('change', applyFilter);
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        if (searchInput) searchInput.value = '';
                        if (categoryFilter) categoryFilter.value = '';
                        applyFilter();
                        searchInput?.focus();
                    });
                }
            })();
        });
    </script>
</body>

</html>