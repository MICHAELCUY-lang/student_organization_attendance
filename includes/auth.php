  // includes/auth.php
  <?php
  /**
   * Check if user is logged in, if not redirect to login page
   * 
   * @return void
   */
  function requireLogin() {
      if (!isLoggedIn()) {
          setFlashMessage('login_required', 'Please login to access this page', 'warning');
          redirect(BASE_URL . '/login.php');
      }
  }

  /**
   * Check if user has specified role, if not redirect to dashboard
   * 
   * @param string $role
   * @return void
   */
  function requireRole($role) {
      requireLogin();
      if (!hasRole($role)) {
          setFlashMessage('access_denied', 'You do not have permission to access this page', 'danger');
          redirect(BASE_URL . '/dashboard.php');
      }
  }