<head>

<link rel="stylesheet" href="./dist/css/login.css">



</head>

<body>
<div class="container right-panel-active" id="container">
	<div class="form-container sign-up-container">
		<form action="#">
			<h1>Zarejestruj się</h1><br>
			<input type="text" placeholder="Name" />
			<input type="email" placeholder="Email" />
			<input type="password" placeholder="Password" />
			<button>Sign Up</button>
		</form>
	</div>
	<div class="form-container sign-in-container">
		<form action="#">
			<h1>Zaloguj się</h1>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>or use your account</span>
			<input type="email" placeholder="Email" />
			<input type="password" placeholder="Password" />
			<a href="#">Forgot your password?</a>
			<button>Sign In</button>
		</form>
	</div>
	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h1>Masz już konto?</h1>
				<p>Kliknij tutaj i zaloguj się!</p>
				<button class="ghost" id="signIn">LOGOWANIE</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h1>Nie masz konta?</h1>
				<p>Kliknij tutaj i zarejestruj się</p>
				<button class="ghost" id="signUp">REJESTRACJA</button>
			</div>
		</div>
	</div>
</div>

<script type=text/javascript>
const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
	container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
	container.classList.remove("right-panel-active");
});

</script>
</body>