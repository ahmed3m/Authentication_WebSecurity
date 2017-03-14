<?php
require_once('../../../private/initialize.php');
require_login();

if(!isset($_GET['id'])) {
  redirect_to('index.php');
}
$users_result = find_user_by_id($_GET['id']);
// No loop, only one result
$user = db_fetch_assoc($users_result);
$original_username = $user['username']; // used when checking for uniqueness
// Set default values for all variables the page needs.
$errors = array();

if(is_post_request() && request_is_same_domain()) {
  ensure_csrf_token_valid();

  // Confirm that values are present before accessing them.
  if(isset($_POST['first_name'])) { $user['first_name'] = $_POST['first_name']; }
  if(isset($_POST['last_name'])) { $user['last_name'] = $_POST['last_name']; }
  if(isset($_POST['username'])) { $user['username'] = $_POST['username']; }
  if(isset($_POST['email'])) { $user['email'] = $_POST['email']; }
  if(isset($_POST['password'])) { $user['password'] = $_POST['password']; }
  if(isset($_POST['password_confirmation'])) { $user['password_confirmation'] = $_POST['password_confirmation']; }

  // Perform Validations
  // validating the first name
  if (is_blank($user['first_name'])) {
    $errors[] = "First name cannot be blank.";
  } elseif(!preg_match('/\A[A-Za-z\s\-,\.\']+\Z/', $user['first_name'])) {
    $errors[] = "First name can only contain letters, spaces, and the following symbols: - , . '";
  } elseif (!has_length($user['first_name'], ['min' => 2, 'max' => 255])) {
    $errors[] = "First name must be between 2 and 255 characters.";
  }
  // validating the last name
  if (is_blank($user['last_name'])) {
    $errors[] = "Last name cannot be blank.";
  } elseif(!preg_match('/\A[A-Za-z\s\-,\.\']+\Z/', $user['last_name'])) {
    $errors[] = "Last name can only contain letters, spaces, and the following symbols: - , . '";
  } elseif (!has_length($user['last_name'], ['min' => 2, 'max' => 255])) {
    $errors[] = "Last name must be between 2 and 255 characters.";
  }

  // validating the username
  if(is_blank($user['username'])) {
    $errors[] = "Username cannot be blank.";
  } elseif($user['username'] != $original_username && !is_unique_username($user['username'])) {
    $errors[] = "Username already exists.";
  } elseif(!has_valid_username_format($user['username'])) {
    $errors[] = "Username can only contain letters, numbers, and the following symbol: _";
  }

  // validating the email
  if(is_blank($user['email'])) {
    $errors[] = "Email cannot be blank.";
  } elseif(!has_valid_email_format($user['email'])) {
    $errors[] = "Email can only contain letters, numbers, and the following symbols: _ - @ .";
  }

  // validating the password
  if(is_blank($user['password'])) {
    $errors[] = "Password cannot be blank.";
  } elseif(!has_length($user['password'], ['min' => 12])) {
    $errors[] = "Password must be at least 12 characters long.";
  } elseif(!preg_match('/[A-Z]/', $user['password'])) {
    $errors[] = "Password must contain at least 1 uppercase letter.";
  } elseif(!preg_match('/[a-z]/', $user['password'])) {
    $errors[] = "Password must contain at least 1 lowercase letter.";
  } elseif(!preg_match('/[0-9]/', $user['password'])) {
    $errors[] = "Password must contain at least 1 number.";
  } elseif(!preg_match('/[^A-Za-z0-9\s]/', $user['password'])) {
    $errors[] = "Password must contain at least 1 symbol.";
  } elseif(is_blank($user['password_confirmation'])) {
    $errors[] = "Password confirmation cannot be blank.";
  } elseif($user['password'] != $user['password_confirmation']) {
    $errors[] = "Password and password confirmation don't match.";
  }

  if(empty($errors)) {
    $result = update_user($user);
    if($result === true) {
      redirect_to('show.php?id=' . $user['id']);
    } else {
      $errors = $result;
    }
  }
}
?>
<?php $page_title = 'Staff: Edit User ' . $user['first_name'] . " " . $user['last_name']; ?>
<?php include(SHARED_PATH . '/staff_header.php'); ?>

<div id="main-content">
  <a href="index.php">Back to Users List</a><br />

  <h1>Edit User: <?php echo h($user['first_name']) . " " . h($user['last_name']); ?></h1>

  <?php echo display_errors($errors); ?>

  <form action="edit.php?id=<?php echo h(u($user['id'])); ?>" method="post">
    <?php echo csrf_token_tag(); ?>
    First name:<br />
    <input type="text" name="first_name" value="<?php echo h($user['first_name']); ?>" /><br />
    Last name:<br />
    <input type="text" name="last_name" value="<?php echo h($user['last_name']); ?>" /><br />
    Username:<br />
    <input type="text" name="username" value="<?php echo h($user['username']); ?>" /><br />
    Email:<br />
    <input type="text" name="email" value="<?php echo h($user['email']); ?>" /><br />
    Password:<br />
    <input type="password" name="password" /><br />
    Password Confirmation:<br />
    <input type="password" name="password_confirmation" /><br />
    <p>Passwords should be at least 12 characters and include at least one uppercase letter, lowercase letter, number, and symbol.</p>
    <br />
    <input type="submit" name="submit" value="Update"  />
  </form>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
