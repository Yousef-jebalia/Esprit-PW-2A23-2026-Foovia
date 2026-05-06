<?php
include '../../Controller/menu_module/controle_ingrediant.php';

$controller = new Controller_ingrediant();
$error = "";
$ingrediantData = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (
        isset($_POST["id_ing"], $_POST["name_ing"], $_POST["prot_ing"], $_POST["fat_ing"], $_POST["carb_ing"], $_POST["cal_ing"])
    ) {
        $id = (int)$_POST["id_ing"];
        $imagePath = isset($_POST["current_img_ing"]) ? $_POST["current_img_ing"] : "";

        if (isset($_FILES['img_ing']) && $_FILES['img_ing']['error'] === UPLOAD_ERR_OK) {
            $absoluteUploadDir = __DIR__ . '/assets/images/ingredients/';
            $relativeUploadDir = 'assets/images/ingredients/';

            if (!is_dir($absoluteUploadDir)) {
                mkdir($absoluteUploadDir, 0755, true);
            }

            $fileName = basename($_FILES['img_ing']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $tmpPath = $_FILES['img_ing']['tmp_name'];
            $mimeType = mime_content_type($tmpPath);

            if (!in_array($fileExt, $allowedExts) || !in_array($mimeType, $allowedMimes)) {
                $error = "Invalid image file format. Only JPG, PNG, GIF, WebP allowed.";
            } else {
                $newFileName = uniqid('ing_', true) . '.' . $fileExt;
                $absoluteUploadPath = $absoluteUploadDir . $newFileName;
                if (move_uploaded_file($tmpPath, $absoluteUploadPath)) {
                    $imagePath = $relativeUploadDir . $newFileName;
                } else {
                    $error = "Failed to upload image file.";
                }
            }
        } elseif (isset($_FILES['img_ing']) && $_FILES['img_ing']['error'] !== UPLOAD_ERR_NO_FILE) {
            $error = "Upload error code: " . (int)$_FILES['img_ing']['error'];
        }

        if (empty($error)) {
            $ingrediant = new Ingrediant(
                $id,
                trim($_POST["name_ing"]),
                (float)$_POST["prot_ing"],
                trim($_POST["fat_ing"]),
                trim($_POST["carb_ing"]),
                trim($_POST["cal_ing"]),
                $imagePath
            );

            $controller->update_ingrediant($ingrediant);
            header('Location: form-elements-ingrediant.php');
            exit;
        }

        $ingrediantData = [
            'id_ing' => $id,
            'name_ing' => $_POST["name_ing"],
            'prot_ing' => $_POST["prot_ing"],
            'fat_ing' => $_POST["fat_ing"],
            'carb_ing' => $_POST["carb_ing"],
            'cal_ing' => $_POST["cal_ing"],
            'img_ing' => $imagePath
        ];
    } else {
        $error = "Missing form data.";
    }
}

if ($ingrediantData === null) {
    if (!isset($_GET["id_ing"]) || !is_numeric($_GET["id_ing"])) {
        $error = "Invalid ingrediant ID.";
    } else {
        $ingrediantData = $controller->get_ingrediant_by_id((int)$_GET["id_ing"]);
        if (!$ingrediantData) {
            $error = "Ingrediant not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Ingrediant</title>
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width:900px;padding-top:30px;padding-bottom:30px;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 style="margin:0;">Edit Ingrediant</h5>
                <a href="form-elements-ingrediant.php" class="btn btn-secondary btn-sm">Back to list</a>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!empty($ingrediantData)): ?>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="id_ing" value="<?php echo (int)$ingrediantData['id_ing']; ?>">
                        <input type="hidden" name="current_img_ing" value="<?php echo htmlspecialchars($ingrediantData['img_ing']); ?>">

                        <div class="form-group">
                            <label>Ingrediant ID</label>
                            <input type="text" class="form-control" value="<?php echo (int)$ingrediantData['id_ing']; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Ingrediant Name</label>
                            <input type="text" name="name_ing" class="form-control" value="<?php echo htmlspecialchars($ingrediantData['name_ing']); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Protein</label>
                                <input type="text" name="prot_ing" class="form-control" value="<?php echo htmlspecialchars($ingrediantData['prot_ing']); ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Fat</label>
                                <input type="text" name="fat_ing" class="form-control" value="<?php echo htmlspecialchars($ingrediantData['fat_ing']); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Carbs</label>
                            <input type="text" name="carb_ing" class="form-control" value="<?php echo htmlspecialchars($ingrediantData['carb_ing']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Calories</label>
                            <input type="text" name="cal_ing" class="form-control" value="<?php echo htmlspecialchars($ingrediantData['cal_ing']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Current Image</label><br>
                            <?php if (!empty($ingrediantData['img_ing'])): ?>
                                <img src="<?php echo htmlspecialchars($ingrediantData['img_ing']); ?>" alt="Current ingrediant image" style="width:100px;height:100px;object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                                <span>No image</span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Upload New Image (optional)</label>
                            <input type="file" name="img_ing" id="imageInputIngEdit" class="form-control">
                            <small class="form-text text-muted">Choose a new image to replace the current one.</small>
                            <div id="imagePreviewIngEdit" class="mt-3" style="display:none;">
                                <img id="previewImgIngEdit" style="width:120px;height:120px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        const editImageInput = document.getElementById('imageInputIngEdit');
        if (editImageInput) {
            editImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        document.getElementById('previewImgIngEdit').src = event.target.result;
                        document.getElementById('imagePreviewIngEdit').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                } else if (file) {
                    alert('Please select a valid image file');
                    document.getElementById('previewImgIngEdit').src = '';
                    document.getElementById('imagePreviewIngEdit').style.display = 'none';
                } else {
                    document.getElementById('previewImgIngEdit').src = '';
                    document.getElementById('imagePreviewIngEdit').style.display = 'none';
                }
            });
        }

        (function() {
            const form = document.querySelector('form[enctype="multipart/form-data"]');
            if (!form) return;

            const nameInput = form.querySelector('input[name="name_ing"]');
            const protInput = form.querySelector('input[name="prot_ing"]');
            const fatInput = form.querySelector('input[name="fat_ing"]');
            const carbInput = form.querySelector('input[name="carb_ing"]');
            const calInput = form.querySelector('input[name="cal_ing"]');
            const floatFields = [protInput, fatInput, carbInput, calInput];

            const restrictDigits = function(input, maxLength) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '').slice(0, maxLength);
                });
            };

            const restrictText = function(input, maxLength) {
                input.addEventListener('input', function() {
                    this.value = this.value.replace(/[^A-Za-z\s]/g, '').slice(0, maxLength);
                });
            };

            const restrictFloatField = function(input) {
                input.addEventListener('input', function() {
                    let value = this.value.replace(',', '.').replace(/[^0-9.]/g, '');

                    const firstDotIndex = value.indexOf('.');
                    if (firstDotIndex !== -1) {
                        value = value.slice(0, firstDotIndex + 1) + value.slice(firstDotIndex + 1).replace(/\./g, '');
                    }

                    const parts = value.split('.');
                    if (parts.length > 1) {
                        parts[1] = parts[1].slice(0, 2);
                        value = parts[0] + '.' + parts[1];
                    }

                    if (value !== '' && Number(value) > 2000) {
                        value = '2000';
                    }

                    this.value = value;
                });
            };

            const isValidStep001 = function(value) {
                const scaled = Number(value) * 100;
                return Math.abs(scaled - Math.round(scaled)) < 1e-9;
            };

            const isTextValue = function(value) {
                return /[A-Za-z]/.test(value);
            };

            restrictText(nameInput, 20);
            floatFields.forEach(restrictFloatField);

            form.addEventListener('submit', function(e) {
                const errors = [];

                const nameRaw = nameInput.value.trim();
                if (nameRaw.length > 20) {
                    errors.push('Name max length is 20.');
                }
                if (nameRaw && !isTextValue(nameRaw)) {
                    errors.push('Name must be a string value.');
                }

                floatFields.forEach(function(field) {
                    const label = field.name === 'prot_ing' ? 'Protein' :
                        field.name === 'fat_ing' ? 'Fat' :
                        field.name === 'carb_ing' ? 'Carbs' : 'Calories';
                    const raw = field.value.trim();
                    const val = Number(raw);

                    if (raw === '' || !Number.isFinite(val)) {
                        errors.push(label + ' must be a float value.');
                        return;
                    }
                    if (!isValidStep001(raw)) {
                        errors.push(label + ' must use a 0.01 step.');
                    }
                    if (val > 2000) {
                        errors.push(label + ' max value is 2000.');
                    }
                });

                if (errors.length > 0) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        })();
    </script>
</body>
</html>
