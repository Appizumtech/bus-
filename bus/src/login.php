<?php
require __DIR__ . '/includes/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = $_POST['email'] ?? '';
	$password = $_POST['password'] ?? '';
	$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND active = 1 AND role = 'customer' LIMIT 1");
	$stmt->execute([$email]);
	$user = $stmt->fetch();
	if ($user && $password === $user['password']) {
		$_SESSION['user_id'] = (int)$user['id'];
		$_SESSION['role'] = $user['role'];
		header('Location: dashboard.php');
		exit;
	} else {
		$error = 'Invalid credentials';
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>User Login</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
	<div class="container py-5">
		<div class="row justify-content-center">
			<div class="col-md-4">
				<div class="card">
					<div class="card-header">User Login</div>
					<div class="card-body">
						<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
						<form method="post">
							<div class="mb-3">
								<label class="form-label">Email</label>
								<input type="email" class="form-control" name="email" required>
							</div>
							<div class="mb-3">
								<label class="form-label">Password</label>
								<input type="password" class="form-control" name="password" required>
							</div>
							<button type="submit" class="btn btn-primary w-100">Login</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>