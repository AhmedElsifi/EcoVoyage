<?php

class AuthController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new Users();
    }

    public function register($role = null)
    {
        if ($role === null) {
            View::load("auth/register", ['title' => 'Join EcoVoyage']);
            return;
        }

        $allowedRoles = ['traveler', 'guide', 'auditor'];
        if (!in_array($role, $allowedRoles)) {
            View::load("auth/register", ['title' => 'Join EcoVoyage']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeRegistration($role);
            return;
        }

        $viewMap = [
            'traveler' => 'auth/register_traveler',
            'guide' => 'auth/register_guide',
            'auditor' => 'auth/register_auditor'
        ];

        View::load($viewMap[$role], [
            'title' => 'Register as ' . ucfirst($role),
            'errors' => []
        ]);
    }

    private function storeRegistration($role)
    {
        $errors = [];

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!Validator::required($name)) {
            $errors[] = 'Full name is required.';
        } elseif (!Validator::alphaSpaces($name)) {
            $errors[] = 'Name may only contain letters and spaces.';
        }

        if (!Validator::email($email)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (!Validator::minLength($password, 6)) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if (!Validator::match($password, $confirm)) {
            $errors[] = 'Passwords do not match.';
        }

        if ($role === 'traveler') {
            $nationality = trim($_POST['nationality'] ?? '');
            if (!Validator::required($nationality)) {
                $errors[] = 'Nationality is required.';
            } elseif (!Validator::noHtml($nationality)) {
                $errors[] = 'Nationality must not contain HTML.';
            }

            $phone = trim($_POST['phone'] ?? '');
            if ($phone !== '' && !Validator::phone($phone)) {
                $errors[] = 'Phone number is not valid.';
            }

            $dob = $_POST['date_of_birth'] ?? '';
            if ($dob !== '' && !Validator::date($dob)) {
                $errors[] = 'Date of birth is not a valid date.';
            }
        }

        if ($role === 'guide') {
            $country = trim($_POST['country_of_residence'] ?? '');
            if (!Validator::required($country)) {
                $errors[] = 'Country of residence is required.';
            } elseif (!Validator::noHtml($country)) {
                $errors[] = 'Country must not contain HTML.';
            }

            $bio = trim($_POST['bio'] ?? '');
            if (!Validator::required($bio)) {
                $errors[] = 'Bio is required.';
            } elseif (!Validator::minLength($bio, 20)) {
                $errors[] = 'Bio must be at least 20 characters.';
            } elseif (!Validator::noHtml($bio)) {
                $errors[] = 'Bio must not contain HTML.';
            }

            $experience = $_POST['years_of_experience'] ?? '';
            if (!Validator::required($experience)) {
                $errors[] = 'Years of experience is required.';
            } elseif (!Validator::numeric($experience)) {
                $errors[] = 'Experience must be a number.';
            }

            $phone = trim($_POST['phone'] ?? '');
            if ($phone !== '' && !Validator::phone($phone)) {
                $errors[] = 'Phone number is not valid.';
            }

            if (!Validator::fileRequired('identity_doc')) {
                $errors[] = 'Identity document is required.';
            } elseif (!Validator::fileType('identity_doc', ['pdf', 'jpg', 'jpeg', 'png'])) {
                $errors[] = 'ID document must be a PDF, JPG, or PNG.';
            } elseif (!Validator::fileMaxSize('identity_doc', 2048)) {
                $errors[] = 'ID document must be under 2 MB.';
            }
        }

        if ($role === 'auditor') {
            $region = trim($_POST['assigned_region'] ?? '');
            if (!Validator::required($region)) {
                $errors[] = 'Assigned region is required.';
            } elseif (!Validator::noHtml($region)) {
                $errors[] = 'Region must not contain HTML.';
            }

            $phone = trim($_POST['phone'] ?? '');
            if ($phone !== '' && !Validator::phone($phone)) {
                $errors[] = 'Phone number is not valid.';
            }

            if (!Validator::fileRequired('cv')) {
                $errors[] = 'CV is required.';
            } elseif (!Validator::fileType('cv', ['pdf'])) {
                $errors[] = 'CV must be a PDF file.';
            } elseif (!Validator::fileMaxSize('cv', 2048)) {
                $errors[] = 'CV must be under 2 MB.';
            }
        }

        if (!empty($errors)) {
            $viewMap = [
                'traveler' => 'auth/register_traveler',
                'guide' => 'auth/register_guide',
                'auditor' => 'auth/register_auditor'
            ];
            View::load($viewMap[$role], ['errors' => $errors]);
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'phone' => $_POST['phone'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
        ];

        if ($this->usersModel->register($userData)) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        } else {
            $errors[] = 'Registration failed. Email might already be in use.';
            $viewMap = [
                'traveler' => 'auth/register_traveler',
                'guide' => 'auth/register_guide',
                'auditor' => 'auth/register_auditor'
            ];
            View::load($viewMap[$role], ['errors' => $errors]);
        }
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectByRole($_SESSION['role']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            View::load('auth/login', ['errors' => []]);
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email.';
        }

        if ($password === '') {
            $errors[] = 'Password is required.';
        }

        if (!empty($errors)) {
            View::load('auth/login', ['errors' => $errors]);
            return;
        }

        if ($this->usersModel->login($email, $password)) {
            $this->redirectByRole($_SESSION['role']);
        } else {
            $errors[] = 'Invalid email or password.';
            View::load('auth/login', ['errors' => $errors]);
        }
    }

    private function redirectByRole($role)
    {
        switch ($role) {
            case 'super_admin':
                header('Location: ' . BASE_URL . 'admin/dashboard');
                break;
            case 'regional_auditor':
                header('Location: ' . BASE_URL . 'auditor/dashboard');
                break;
            case 'guide':
                header('Location: ' . BASE_URL . 'guide/dashboard');
                break;
            case 'traveler':
                header('Location: ' . BASE_URL . 'traveler/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . 'guest/index');
        }
        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        
        header('Location: ' . BASE_URL . 'guest/index');
    }
}
