<?php
require_once __DIR__ . '/../data/config.php';

// If already logged in, redirect to backend dashboard
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both your username and password.';
    } elseif (verify_login($username, $password)) {
        session_regenerate_id(true);
        $_SESSION['gnarly_admin_logged_in'] = true;
        $_SESSION['gnarly_admin_username'] = $username;
        $_SESSION['gnarly_login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        usleep(300000);
        $error = 'Invalid username or password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BACKEND LOGIN | GNARLY MTB NEPAL</title>
  <meta name="robots" content="noindex, nofollow" />
  <link rel="icon" type="image/png" href="../assets/branding/favicon.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: { DEFAULT: "#F5EFEB", hover: "#E8DFD8" },
            canvas: "#2A4E7A",
            surface: { DEFAULT: "#1E3A5F", secondary: "#162E4D", elevated: "#345C8C" }
          },
          fontFamily: {
            display: ["Anton", "sans-serif"],
            heading: ["Oswald", "sans-serif"],
            body: ["Roboto", "sans-serif"]
          }
        }
      }
    };
  </script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      background: radial-gradient(circle at 50% 20%, #2A4E7A 0%, #162E4D 100%);
      min-height: 100vh;
    }
  </style>
</head>
<body class="text-gray-100 flex items-center justify-center p-4 antialiased">

  <div class="w-full max-w-md">
    <!-- Top Branding -->
    <div class="text-center mb-8">
      <a href="../index.html" class="inline-flex items-center space-x-3 group" title="Return to Website">
        <img src="../assets/branding/logo.png" alt="Gnarly MTB Nepal" class="w-14 h-14 object-contain rounded-full shadow-xl border border-white/20 group-hover:scale-105 transition-transform" />
      </a>
      <h1 class="font-display text-3xl uppercase tracking-wider text-white mt-4">
        GNARLY <span class="text-[#F5EFEB]">MTB</span>
      </h1>
      <p class="font-heading text-xs uppercase tracking-widest text-[#F5EFEB]/70 mt-1">
        Backend Management Portal
      </p>
    </div>

    <!-- Login Card -->
    <div class="bg-[#1E3A5F]/95 border border-white/15 rounded-xl shadow-2xl p-8 backdrop-blur-md relative overflow-hidden">
      <!-- Accent bar -->
      <div class="absolute top-0 left-0 right-0 h-1 bg-[#F5EFEB]"></div>

      <div class="mb-6">
        <h2 class="font-heading text-xl uppercase font-bold tracking-wide text-white flex items-center gap-2">
          <i data-lucide="shield-check" class="w-5 h-5 text-[#F5EFEB]"></i>
          Owner Authentication
        </h2>
        <p class="font-body text-xs text-gray-300 mt-1">
          Enter credentials to manage site imagery and gallery dispatches.
        </p>
      </div>

      <?php if (!empty($error)): ?>
      <div class="mb-5 p-3.5 rounded bg-red-950/80 border border-red-500/50 flex items-start gap-3 text-red-200 text-sm">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 shrink-0 mt-0.5"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php" class="space-y-5" autocomplete="on">
        <div>
          <label for="username" class="block font-heading text-xs uppercase tracking-wider text-gray-300 mb-1.5">
            Username
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i data-lucide="user" class="w-4 h-4"></i>
            </div>
            <input 
              type="text" 
              id="username" 
              name="username" 
              required 
              autofocus
              placeholder="Enter username" 
              value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
              class="w-full pl-10 pr-4 py-2.5 bg-[#162E4D] border border-white/20 rounded text-white placeholder-gray-400 focus:outline-none focus:border-[#F5EFEB] focus:ring-1 focus:ring-[#F5EFEB] font-body text-sm transition-colors"
            />
          </div>
        </div>

        <div>
          <label for="password" class="block font-heading text-xs uppercase tracking-wider text-gray-300 mb-1.5">
            Password
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
              <i data-lucide="lock" class="w-4 h-4"></i>
            </div>
            <input 
              type="password" 
              id="password" 
              name="password" 
              required 
              placeholder="••••••••••••" 
              class="w-full pl-10 pr-10 py-2.5 bg-[#162E4D] border border-white/20 rounded text-white placeholder-gray-400 focus:outline-none focus:border-[#F5EFEB] focus:ring-1 focus:ring-[#F5EFEB] font-body text-sm transition-colors"
            />
            <button 
              type="button" 
              id="togglePassword" 
              class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-white transition-colors" 
              aria-label="Toggle password visibility">
              <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
            </button>
          </div>
        </div>

        <button 
          type="submit" 
          class="w-full py-3 px-4 bg-[#F5EFEB] hover:bg-[#E8DFD8] text-black font-heading text-sm font-bold uppercase tracking-widest rounded transition-all duration-200 transform hover:-translate-y-0.5 shadow-lg flex items-center justify-center gap-2">
          <span>Log In to Backend</span>
          <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </button>
      </form>

      <!-- Back to website link -->
      <div class="mt-6 pt-4 border-t border-white/10 text-center">
        <a href="../index.html" class="inline-flex items-center gap-1.5 text-xs font-heading uppercase tracking-wider text-gray-400 hover:text-[#F5EFEB] transition-colors">
          <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
          <span>Return to Live Website</span>
        </a>
      </div>
    </div>

    <!-- Security notice -->
    <p class="text-center text-[11px] text-gray-400 mt-6 tracking-wide">
      Protected by Gnarly MTB Nepal Security System • cPanel Self-Contained
    </p>
  </div>

  <script>
    lucide.createIcons();

    // Toggle password visibility
    const toggleBtn = document.getElementById('togglePassword');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (toggleBtn && pwdInput) {
      toggleBtn.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
        lucide.createIcons();
      });
    }
  </script>
</body>
</html>
