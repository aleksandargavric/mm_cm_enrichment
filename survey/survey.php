<?php
// survey.php

// Flag to trigger saved modal after POST processing.
$saved = false;

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather survey data, annotation responses, updated thinking values, value fields, non-json responses, and TAM responses.
    $data = [
        'survey'            => $_POST['survey'] ?? [],
        'tam'               => $_POST['tam'] ?? [],
        'annotations'       => $_POST['annotations'] ?? [],
        'thinking'          => $_POST['thinking'] ?? [],
        'value'             => $_POST['value'] ?? [],
        'non_json_response' => $_POST['non_json_response'] ?? []
    ];

    // Create the /responses directory if it does not exist.
    $responseDir = __DIR__ . '/responses';
    if (!file_exists($responseDir)) {
        mkdir($responseDir, 0777, true);
    }

    // Save the response with a unique filename (using a random 10-digit number).
    $randomNumber = mt_rand(1000000000, 9999999999);
    $filename = $responseDir . '/response_' . $randomNumber . '.json';
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));

    // Set flag to show modal after page reload.
    $saved = true;
}

// Load the JSON file with annotation data.
$jsonFilePath = __DIR__ . '../ontouml/ollama_responses_2.json';
$jsonContent  = file_get_contents($jsonFilePath);
$jsonData     = json_decode($jsonContent, true);

// Pagination logic for annotation review sections (each page shows 10 sections)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$annotationsPerPage = 10;
$jsonKeys = array_keys($jsonData);
$totalSections = count($jsonKeys);
$totalPages = ceil($totalSections / $annotationsPerPage);
$startIndex = ($page - 1) * $annotationsPerPage;
// Slice while preserving keys
$slicedKeys = array_slice($jsonKeys, $startIndex, $annotationsPerPage, true);

// If form was posted, capture posted values to display in the form.
$postedThinking    = $_POST['thinking'] ?? [];
$postedAnnotations = $_POST['annotations'] ?? [];
$postedValue       = $_POST['value'] ?? [];
$postedNonJson     = $_POST['non_json_response'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Annotation Review Survey</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Bootstrap CSS from CDN -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
  <style>
    /* Simple styling for annotation items so that the word and its flag buttons appear close together */
    .annotation-item {
        display: inline-block;
        margin: 5px;
        padding: 5px;
        border: 1px solid #ccc;
    }
    /* Ensure the textarea is scrollable if content overflows */
    textarea {
        overflow: auto;
    }
  </style>
</head>
<body>
<div class="container mt-4 mb-5">
  <h1>Annotation Review Survey</h1>
  <!-- Using GET parameter "page" for pagination; you may want to also persist the current page in a hidden field if needed -->
  <form method="post" action="?page=<?php echo $page; ?>">
    <?php if ($page === 1): ?>
    <!-- Survey Section (Questionnaire) only on page 1 -->
    <div class="card mb-3">
      <div class="card-header">
        Survey
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="sex">Sex</label>
          <select class="form-control" name="survey[sex]" id="sex" required>
            <option value="">Select...</option>
            <option value="male" <?php echo (($_POST['survey']['sex'] ?? '') === 'male') ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo (($_POST['survey']['sex'] ?? '') === 'female') ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo (($_POST['survey']['sex'] ?? '') === 'other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label for="age">Age</label>
          <input type="number" class="form-control" name="survey[age]" id="age" value="<?php echo htmlspecialchars($_POST['survey']['age'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
          <label for="experience">Experience in Conceptual Modeling Field</label>
          <select class="form-control" name="survey[experience]" id="experience" required>
            <option value="">Select...</option>
            <option value="PhD holder" <?php echo (($_POST['survey']['experience'] ?? '') === 'PhD holder') ? 'selected' : ''; ?>>PhD holder</option>
            <option value="PhD candidate" <?php echo (($_POST['survey']['experience'] ?? '') === 'PhD candidate') ? 'selected' : ''; ?>>PhD candidate</option>
            <option value="Master student" <?php echo (($_POST['survey']['experience'] ?? '') === 'Master student') ? 'selected' : ''; ?>>Master student</option>
            <option value="Bachelor student" <?php echo (($_POST['survey']['experience'] ?? '') === 'Bachelor student') ? 'selected' : ''; ?>>Bachelor student</option>
            <option value="No experience" <?php echo (($_POST['survey']['experience'] ?? '') === 'No experience') ? 'selected' : ''; ?>>No experience</option>
          </select>
        </div>
      </div>
    </div>
    <!-- TAM Survey Questions -->
    <div class="card mb-3">
      <div class="card-header">
        Technology Acceptance Model (TAM) Survey
      </div>
      <div class="card-body">
        <div class="form-group">
          <label>Perceived Usefulness (PU): Does MMeCM enhance your conceptual modeling tasks?</label>
          <div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <label class="radio-inline mr-2">
                  <input type="radio" name="tam[pu]" value="<?php echo $i; ?>" <?php echo (isset($_POST['tam']['pu']) && $_POST['tam']['pu'] == $i) ? 'checked' : ''; ?>> <?php echo $i; ?>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <label>Perceived Ease of Use (PEOU): Is MMeCM intuitive and user-friendly?</label>
          <div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <label class="radio-inline mr-2">
                  <input type="radio" name="tam[peou]" value="<?php echo $i; ?>" <?php echo (isset($_POST['tam']['peou']) && $_POST['tam']['peou'] == $i) ? 'checked' : ''; ?>> <?php echo $i; ?>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <label>Attitude Toward Using (ATT): Would you consider using MMeCM in your workflow?</label>
          <div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <label class="radio-inline mr-2">
                  <input type="radio" name="tam[att]" value="<?php echo $i; ?>" <?php echo (isset($_POST['tam']['att']) && $_POST['tam']['att'] == $i) ? 'checked' : ''; ?>> <?php echo $i; ?>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <label>Behavioral Intention (BI): Would you recommend MMeCM to colleagues?</label>
          <div>
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <label class="radio-inline mr-2">
                  <input type="radio" name="tam[bi]" value="<?php echo $i; ?>" <?php echo (isset($_POST['tam']['bi']) && $_POST['tam']['bi'] == $i) ? 'checked' : ''; ?>> <?php echo $i; ?>
              </label>
            <?php endfor; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php if ($page > 1): ?>
     <!-- Pagination Navigation -->
  <nav aria-label="Annotation review pages">
    <ul class="pagination justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>


<br>

<br>
<?php endif; ?>
    <!-- Annotation Review Sections (paginated) -->
    <?php foreach ($slicedKeys as $key): 
      $section = $jsonData[$key];
    ?>
    <div class="card mb-3">
      <div class="card-header">
        Annotation Review: <?php echo htmlspecialchars($key); ?>
      </div>
      <div class="card-body">
      <?php
  $imageDir = __DIR__ . "/ontouml/ontouml-models/models/{$key}/original-diagrams/";
  $relativeImageDir = "ontouml/ontouml-models/models/{$key}/original-diagrams/";

  $imageFiles = [];
  if (is_dir($imageDir)) {
      $imageFiles = glob($imageDir . "*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
  }
?>

<?php if (!empty($imageFiles)): ?>
  <div class="mt-3">
    <h5>Diagrams</h5>
    <div class="d-flex flex-wrap">
      <?php foreach ($imageFiles as $imagePath): 
        $relativePath = $relativeImageDir . basename($imagePath);
      ?>
        <div class="m-2">
          <img src="<?php echo $relativePath; ?>" alt="Diagram" style="max-height: 150px; border: 1px solid #ccc; padding: 2px;">
          <p class="text-center small"><?php echo htmlspecialchars(basename($imagePath)); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php else: ?>
  <p><em>No original diagrams found for this model.</em></p>
<?php endif; ?>

        <!-- Editable "The Thinking" textarea -->
        <div class="form-group">
          <label for="thinking_<?php echo htmlspecialchars($key); ?>">The Thinking:</label>
          <textarea class="form-control" name="thinking[<?php echo htmlspecialchars($key); ?>]" id="thinking_<?php echo htmlspecialchars($key); ?>" rows="3" style="resize: vertical;"><?php echo htmlspecialchars($postedThinking[$key] ?? ($section['the_thinking'] ?? '')); ?></textarea>
        </div>
        <!-- Editable "Value" textarea -->
        <div class="form-group">
          <label for="value_<?php echo htmlspecialchars($key); ?>">Value:</label>
          <textarea class="form-control" name="value[<?php echo htmlspecialchars($key); ?>]" id="value_<?php echo htmlspecialchars($key); ?>" rows="2" style="resize: vertical;"><?php echo htmlspecialchars($postedValue[$key] ?? ($section['value'] ?? '')); ?></textarea>
        </div>
        <p><strong>Business Domain:</strong> <?php echo htmlspecialchars($section['business-domain'] ?? ''); ?><strong>Number of Icon Matches:</strong> <?php echo htmlspecialchars($section['number_of_icon_matches'] ?? ''); ?>; <strong>Number of Visual Matches:</strong> <?php echo htmlspecialchars($section['number_of_visual_matches'] ?? ''); ?>; <strong>Number of Auditory Matches:</strong> <?php echo htmlspecialchars($section['number_of_auditory_matches'] ?? ''); ?>; <strong>Number of Abstract Matches:</strong> <?php echo htmlspecialchars($section['number_of_abstract_matches'] ?? ''); ?></p>


        
        <!-- Annotation review for each term in the readable_name array -->
        <h5>Review Annotations</h5>
        <div class="d-flex flex-wrap">
          <?php if (isset($section['readable_name']) && is_array($section['readable_name'])): ?>
            <?php foreach ($section['readable_name'] as $term): 
              $visual_checked = (isset($postedAnnotations[$key][$term]) && is_array($postedAnnotations[$key][$term]) && in_array('visual_match', $postedAnnotations[$key][$term]))
                                ? true
                                : (isset($section['list_of_visual_matches']) && is_array($section['list_of_visual_matches']) && in_array($term, $section['list_of_visual_matches']));
              $auditory_checked = (isset($postedAnnotations[$key][$term]) && is_array($postedAnnotations[$key][$term]) && in_array('auditory_match', $postedAnnotations[$key][$term]))
                                ? true
                                : (isset($section['list_of_auditory_matches']) && is_array($section['list_of_auditory_matches']) && in_array($term, $section['list_of_auditory_matches']));
              $abstract_checked = (isset($postedAnnotations[$key][$term]) && is_array($postedAnnotations[$key][$term]) && in_array('abstract_match', $postedAnnotations[$key][$term]))
                                ? true
                                : (isset($section['list_of_abstract_matches']) && is_array($section['list_of_abstract_matches']) && in_array($term, $section['list_of_abstract_matches']));
            ?>
              <div class="annotation-item">
                <div><?php echo htmlspecialchars($term); ?></div>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label class="btn btn-sm btn-outline-primary <?php echo $visual_checked ? 'active' : ''; ?>">
                    <input type="checkbox" autocomplete="off"
                           name="annotations[<?php echo htmlspecialchars($key); ?>][<?php echo htmlspecialchars($term); ?>][]"
                           value="visual_match" <?php echo $visual_checked ? 'checked' : ''; ?>> Visual
                  </label>
                  <label class="btn btn-sm btn-outline-secondary <?php echo $auditory_checked ? 'active' : ''; ?>">
                    <input type="checkbox" autocomplete="off"
                           name="annotations[<?php echo htmlspecialchars($key); ?>][<?php echo htmlspecialchars($term); ?>][]"
                           value="auditory_match" <?php echo $auditory_checked ? 'checked' : ''; ?>> Auditory
                  </label>
                  <label class="btn btn-sm btn-outline-success <?php echo $abstract_checked ? 'active' : ''; ?>">
                    <input type="checkbox" autocomplete="off"
                           name="annotations[<?php echo htmlspecialchars($key); ?>][<?php echo htmlspecialchars($term); ?>][]"
                           value="abstract_match" <?php echo $abstract_checked ? 'checked' : ''; ?>> Abstract
                  </label>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Display any additional non-standard fields as editable textareas -->
        <?php
        $known_fields = ['the_thinking', 'value', 'business-domain', 'number_of_icon_matches', 'list_of_icon_matches', 'number_of_visual_matches', 'list_of_visual_matches', 'number_of_auditory_matches', 'list_of_auditory_matches', 'number_of_abstract_matches', 'list_of_abstract_matches', 'readable_name'];
        foreach ($section as $field => $value) {
          if (!in_array($field, $known_fields)) {
              $non_json_value = $postedNonJson[$key][$field] ?? (is_array($value) ? json_encode($value) : $value);
              ?>
              <div class="form-group">
                  <label for="<?php echo htmlspecialchars($key . '_' . $field); ?>"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $field))); ?></label>
                  <textarea class="form-control" name="non_json_response[<?php echo htmlspecialchars($key); ?>][<?php echo htmlspecialchars($field); ?>]" id="<?php echo htmlspecialchars($key . '_' . $field); ?>" rows="3" style="resize: vertical;"><?php echo htmlspecialchars($non_json_value); ?></textarea>
              </div>
              <?php
          }
        }
        ?>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Fixed bottom big save button -->
    <nav class="navbar fixed-bottom navbar-light bg-light">
      <div class="container">
        <button type="submit" class="btn btn-primary btn-lg btn-block">Save</button>
      </div>
    </nav>
  </form>

  <!-- Pagination Navigation -->
  <nav aria-label="Annotation review pages">
    <ul class="pagination justify-content-center mt-3">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>


<br>

<br>

<br>

<!-- Save Confirmation Modal -->
<div class="modal fade" id="savedModal" tabindex="-1" role="dialog" aria-labelledby="savedModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title" id="savedModalLabel">Saved</h5>
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       <div class="modal-body">
         Your response has been saved.
       </div>
       <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       </div>
    </div>
  </div>
</div>

<!-- jQuery, Popper.js, and Bootstrap JS (from CDN) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

<?php if ($saved): ?>
<script>
// On page load, trigger the modal if the file has been saved.
$(document).ready(function(){
    $('#savedModal').modal('show');
});
</script>
<?php endif; ?>
</body>
</html>
