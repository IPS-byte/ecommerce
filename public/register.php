<?php
session_start();
include '../includes/db.php'; // Ensure $mysqli connection is defined
include '../includes/functions.php'; // Must define flash_set() and flash_display()

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Collect and sanitize inputs
  $email = trim($_POST['email'] ?? '');
  $first_name = trim($_POST['first_name'] ?? '');
  $last_name = trim($_POST['last_name'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';
  $role = trim($_POST['role'] ?? 'customer');
  $phone_number = trim($_POST['phone_number'] ?? '');
  $profile_image = $_FILES['profile_image'] ?? null;

  // === INPUT VALIDATION ===
  if (empty($email) || empty($first_name) || empty($last_name) || empty($password) || empty($confirm_password)) {
    flash_set('error', 'All fields are required.');
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('error', 'Please enter a valid email address.');
  } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $first_name) || !preg_match("/^[a-zA-Z-' ]*$/", $last_name)) {
    flash_set('error', 'Names must contain only letters and spaces.');
  } elseif ($password !== $confirm_password) {
    flash_set('error', 'Passwords do not match.');
  } elseif (strlen($password) < 8) {
    flash_set('error', 'Password must be at least 8 characters long.');
  } else {
    // === CHECK IF EMAIL ALREADY EXISTS ===
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
      flash_set('error', 'Database error: ' . $mysqli->error);
      header('Location: register.php');
      exit;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      flash_set('error', 'This email is already registered.');
      $stmt->close();
      header('Location: register.php');
      exit;
    }
    $stmt->close();

    // === HANDLE PROFILE IMAGE UPLOAD ===
    $profile_image_path = null;
    if ($profile_image && $profile_image['error'] === UPLOAD_ERR_OK) {
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      if (in_array($profile_image['type'], $allowed_types)) {
        $ext = pathinfo($profile_image['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('profile_', true) . '.' . $ext;
        $upload_dir = '../uploads/profile_images/';
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
        }
        $target_path = $upload_dir . $new_filename;
        if (move_uploaded_file($profile_image['tmp_name'], $target_path)) {
          $profile_image_path = 'uploads/profile_images/' . $new_filename;
        }
      }
    }

    // === HASH PASSWORD & INSERT USER ===
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insert = $mysqli->prepare("INSERT INTO users (email, password, first_name, last_name, role, phone_number, profile_image, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    if (!$insert) {
      flash_set('error', 'Database error: ' . $mysqli->error);
      header('Location: register.php');
      exit;
    }

    $insert->bind_param('sssssss', $email, $hashedPassword, $first_name, $last_name, $role, $phone_number, $profile_image_path);
    $success = $insert->execute();
    $insert->close();

    if ($success) {
      // Store session info for immediate login
      $_SESSION['user_id'] = $mysqli->insert_id;
      $_SESSION['user_email'] = $email;
      $_SESSION['user_name'] = $first_name . ' ' . $last_name;
      $_SESSION['user_role'] = $role;
      $_SESSION['last_activity'] = time();
      // Redirect to admin dashboards after successful registration
      // redirect based on role
      switch (strtolower($role)) {
        case 'admin':
          header('Location: ../dashboards/admin_dashboard.php');
          break;
        case 'seller':
          header('Location: ../dashboards/seller_dashboard.php');
          break;
        case 'customer':
        default:
          header('Location: ../dashboards/customer_dashboard.php');
          break;
      }
      exit;
    } else {
      flash_set('error', 'Registration failed. Please try again.');
    }
  }

  // Redirect after validation or failure
  header('Location: register.php');
  exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
<?php include '../components/style.php'; ?>
</head>
<body class="bg-theme bg-theme1">
	<!--wrapper-->
	<div class="wrapper">
		<div class="d-flex align-items-center justify-content-center my-5">
			<div class="container-fluid">
				<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col mx-auto">
						<div class="card mb-0">
							<div class="card-body">
								<div class="p-4">
									<div class="mb-3 text-center">
										<img src="assets/images/logo-icon.png" width="60" alt="" />
									</div>
									<div class="text-center mb-4">
										<h5 class="">Dashtrans Admin</h5>
										<p class="mb-0">Please fill the below details to create your account</p>
									</div>
                                    <?php flash_display(); ?>
									<div class="form-body">
										<form class="row g-3" action="register.php" method="POST" enctype="multipart/form-data">

  <!-- First Name -->
  <div class="col-md-6">
    <label for="first_name" class="form-label">First Name</label>
    <input type="text" name="first_name" id="first_name" class="form-control" placeholder="John" required>
  </div>

  <!-- Last Name -->
  <div class="col-md-6">
    <label for="last_name" class="form-label">Last Name</label>
    <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Doe" required>
  </div>

  <!-- Email -->
  <div class="col-12">
    <label for="email" class="form-label">Email Address</label>
    <input type="email" name="email" id="email" class="form-control" placeholder="example@domain.com" required>
  </div>


  <!-- Phone Number -->
  <div class="col-12">
    <label for="phone_number" class="form-label">Phone Number</label>
    <input type="text" name="phone_number" id="phone_number" class="form-control" placeholder="+1 234 567 890">
  </div>

  <!-- Role -->
  <div class="col-md-12">
    <label for="role" class="form-label">Role</label>
    <select name="role" id="role" class="form-select">
      <option value="customer" selected>Customer</option>
      <option value="seller">Seller</option>
      <option value="admin">Admin</option>
    </select>
  </div>



  <!-- Profile Image -->
  <div class="col-12">
    <label for="profile_image" class="form-label">Profile Image</label>
    <input type="file" name="profile_image" id="profile_image" class="form-control">
  </div>
    
  <!-- Password -->
  <div class="col-12">
    <label for="password" class="form-label">Password</label>
    <div class="input-group" id="show_hide_password">
      <input type="password" name="password" id="password" class="form-control border-end-0" placeholder="Enter password" required>
      <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
    </div>
  </div>
    <!-- Confirm Password -->
    <div class="col-12">
    <label for="confirm_password" class="form-label">Confirm Password</label>
    <div class="input-group" id="show_hide_confirm_password">
      <input type="password" name="confirm_password" id="confirm_password" class="form-control border-end-0" placeholder="Confirm password" required>
      <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide
'></i></a>
    </div>
    </div>
  <!-- Terms -->
  <div class="col-12">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="terms" required>
      <label class="form-check-label" for="terms">I agree to the Terms & Conditions</label>
    </div>
  </div>

  <!-- Submit -->
  <div class="col-12">
    <div class="d-grid">
      <button type="submit" class="btn btn-light">Sign Up</button>
    </div>
  </div>

  <!-- Login Redirect -->
  <div class="col-12 text-center">
    <p class="mb-0">Already have an account? <a href="login.php">Sign in here</a></p>
  </div>

</form>

									</div>
									<div class="login-separater text-center mb-5"> <span>OR SIGN UP WITH EMAIL</span>
										<hr/>
									</div>
									<div class="list-inline contacts-social text-center">
										<a href="javascript:;" class="list-inline-item bg-light text-white border-0 rounded-3"><i class="bx bxl-google"></i></a>
									</div>

								</div>
							</div>
						</div>
					</div>
				 </div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!--start switcher-->
	<div class="switcher-wrapper">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<p class="mb-0">Gaussian Texture</p>
			  <hr>
			  
			  <ul class="switcher">
				<li id="theme1"></li>
				<li id="theme2"></li>
				<li id="theme3"></li>
				<li id="theme4"></li>
				<li id="theme5"></li>
				<li id="theme6"></li>
			  </ul>
               <hr>
			  <p class="mb-0">Gradient Background</p>
			  <hr>
			  
			  <ul class="switcher">
				<li id="theme7"></li>
				<li id="theme8"></li>
				<li id="theme9"></li>
				<li id="theme10"></li>
				<li id="theme11"></li>
				<li id="theme12"></li>
				<li id="theme13"></li>
				<li id="theme14"></li>
				<li id="theme15"></li>
			  </ul>
		</div>
	</div>
	<!--end switcher-->
<?php include '../components/script.php'; ?>
    <!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<script>
	$(".switcher-btn").on("click", function() {
		$(".switcher-wrapper").toggleClass("switcher-toggled")
	}), $(".close-switcher").on("click", function() {
		$(".switcher-wrapper").removeClass("switcher-toggled")
	}),


	$('#theme1').click(theme1);
    $('#theme2').click(theme2);
    $('#theme3').click(theme3);
    $('#theme4').click(theme4);
    $('#theme5').click(theme5);
    $('#theme6').click(theme6);
    $('#theme7').click(theme7);
    $('#theme8').click(theme8);
    $('#theme9').click(theme9);
    $('#theme10').click(theme10);
    $('#theme11').click(theme11);
    $('#theme12').click(theme12);
    $('#theme13').click(theme13);
    $('#theme14').click(theme14);
    $('#theme15').click(theme15);

    function theme1() {
      $('body').attr('class', 'bg-theme bg-theme1');
    }

    function theme2() {
      $('body').attr('class', 'bg-theme bg-theme2');
    }

    function theme3() {
      $('body').attr('class', 'bg-theme bg-theme3');
    }

    function theme4() {
      $('body').attr('class', 'bg-theme bg-theme4');
    }
	
	function theme5() {
      $('body').attr('class', 'bg-theme bg-theme5');
    }
	
	function theme6() {
      $('body').attr('class', 'bg-theme bg-theme6');
    }

    function theme7() {
      $('body').attr('class', 'bg-theme bg-theme7');
    }

    function theme8() {
      $('body').attr('class', 'bg-theme bg-theme8');
    }

    function theme9() {
      $('body').attr('class', 'bg-theme bg-theme9');
    }

    function theme10() {
      $('body').attr('class', 'bg-theme bg-theme10');
    }

    function theme11() {
      $('body').attr('class', 'bg-theme bg-theme11');
    }

    function theme12() {
      $('body').attr('class', 'bg-theme bg-theme12');
    }

	function theme13() {
		$('body').attr('class', 'bg-theme bg-theme13');
	  }
	  
	  function theme14() {
		$('body').attr('class', 'bg-theme bg-theme14');
	  }
	  
	  function theme15() {
		$('body').attr('class', 'bg-theme bg-theme15');
	  }

	</script>
</body>
</html>