<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="Andre">
	<title>Dukcapil - Register</title>

	<!-- Bootstrap core CSS -->
	<!-- CSS only -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
		  integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

	<meta name="theme-color" content="#7952b3">

	<style>
		body {
			min-height: 75rem;
			padding-top: 4.5rem;
		}

		.bd-placeholder-img {
			font-size: 1.125rem;
			text-anchor: middle;
			-webkit-user-select: none;
			-moz-user-select: none;
			user-select: none;
		}

		@media (min-width: 768px) {
			.bd-placeholder-img-lg {
				font-size: 3.5rem;
			}
		}
	</style>
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
	<div class="container-fluid">
		<a class="navbar-brand" href="#">Simulator Face Recognition</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
				aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarCollapse">
			<ul class="navbar-nav me-auto mb-2 mb-md-0">
				<li class="nav-item">
					<a class="nav-link active" aria-current="page" href="#">Register</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo base_url('validasiweb'); ?>">Validation</a>
				</li>
			</ul>
		</div>
	</div>
</nav>

<main class="container d-flex justify-content-center">
	<div class="card col-lg-6">
		<div class="card-header">
			<h4>Rekam Data</h4>
		</div>
		<div class="card-body">
			<form id="form-register" action="<?php echo base_url(); ?>" method="post" enctype="multipart/form-data">
				<div class="mb-3">
					<label for="txt-nik" class="form-label">NIK</label>
					<input type="text" class="form-control" id="txt-nik" name="nik" autofocus>
				</div>
				<div class="mb-3">
					<label for="txt-user-id" class="form-label">User ID</label>
					<input type="text" class="form-control" id="txt-user-id" name="user_id">
				</div>
				<div class="mb-3">
					<label for="txt-password" class="form-label">Password</label>
					<input type="password" class="form-control" id="txt-password" name="password">
				</div>
				<div class="mb-3">
					<label for="txt-ip" class="form-label">IP Address</label>
					<input type="text" class="form-control" id="txt-ip" name="ip"
						   value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
				</div>
				<div class="mb-3">
					<label for="txt-file" class="form-label">Photo</label>
					<input class="form-control" type="file" id="txt-file" name="photo">
					<div class="mt-3 mb-3">
						<img id="img-preview" src="<?php echo base_url('public/images/no_image.png') ?>" class="img-fluid img-thumbnail">
					</div>
					<input type="hidden" id="txt-image" name="image">
				</div>

				<div id="div-error" class="text-center text-danger"></div>
				<button type="submit" class="btn btn-primary">REGISTER</button>
			</form>
		</div>
	</div>
</main>

<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
		integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
		crossorigin="anonymous"></script>
<script>

	const form = document.querySelector('#form-register');
	const inputFile = document.querySelector('#txt-file');
	const txtImage = document.querySelector('#txt-image');
	const preview = document.querySelector('#img-preview');

	form.addEventListener('submit', function (e) {
		e.preventDefault(); //jangan submit form melalui php

		const obj = {
			nik: document.querySelector('#txt-nik').value,
			user_id: document.querySelector('#txt-user-id').value,
			password: document.querySelector('#txt-password').value,
			ip: document.querySelector('#txt-ip').value,
			image: txtImage.value
		}

		let myHeaders = new Headers();
		myHeaders.append("Content-Type", "application/json");

		let requestOptions = {
			method: 'POST',
			headers: myHeaders,
			body: JSON.stringify(obj),
			redirect: 'follow'
		};

		const divError = document.querySelector('#div-error');
		const act = form.getAttribute('action');
		fetch(act, requestOptions)
			.then(response => response.json())
			.then(result => {
				const r = result.error;
				const code = r.errorCode;
				const message = r.errorMessage;

				if (parseInt(code) === 6018) {
					alert(message);
					window.location.href = act;
				} else {
					divError.innerHTML = message;
				}
			})
			.catch(error => {
				divError.innerHTML = error;
			});
	});

	inputFile.addEventListener('change', function (e) {
		encodeFile(this);
		preview.src = URL.createObjectURL(e.target.files[0]);
		preview.onload = function() {
			URL.revokeObjectURL(preview.src);
		}
	});

	function encodeFile(element) {
		const reader = new FileReader();
		reader.onloadend = function () {
			txtImage.value = reader.result;
		}
		reader.readAsDataURL(element.files[0]);
	}


</script>

</body>
</html>

