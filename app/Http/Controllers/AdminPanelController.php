<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Classes\ApiResponse;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\UserController;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Repositories\User\UserRepository;
use App\Http\Repositories\Terms\TermsRepository;

class AdminPanelController extends Controller
{
    protected $UserController;
    protected $userRepository;
    protected $TermsRepository;

    public function __construct(UserController $UserController, UserRepository $userRepository, TermsRepository $TermsRepository)
    {
        $this->UserController = $UserController;
        $this->userRepository = $userRepository;
        $this->TermsRepository = $TermsRepository;
    }

    public function index()
    {
        return view('login');
    }

    public function showHome()
    {
        return view('adminPage');
    }

    public function getUsers()
    {
        $response = $this->UserController->show();
        return $response;
    }

    public function searchUsers(Request $request)
    {
        try {
            $search = $request->get('search');
            $users = $this->userRepository->all();
            
            if ($search) {
                $users = $users->filter(function($user) use ($search) {
                    return str_contains(strtolower($user->username), strtolower($search)) ||
                           str_contains(strtolower($user->email), strtolower($search)) ||
                           str_contains($user->id, $search);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $users->values()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function login(Request $request)
    {
        try {
            $loginRequest = app(UserLoginRequest::class);
            $loginRequest->initialize(
                $request->query(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
            
            $response = $this->UserController->login($loginRequest);
            $result = $response->getData();

            if (!$result->success) {
                return response()->json([
                    'type' => 'error',
                    'title' => 'Oops...',
                    'text' => 'Email atau password salah'
                ])->header('Content-Type', 'application/json');
            }

            if ($result->data->status->role !== 'admin') {
                return response()->json([
                    'type' => 'error',
                    'title' => 'Akses Ditolak',
                    'text' => 'Hanya admin yang diizinkan'
                ])->header('Content-Type', 'application/json');
            }

            // Set token in session
            session(['token' => $result->token]);
            session(['user' => (array)$result->data]);

            return response()->json([
                'type' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Login berhasil',
                'redirect' => route('admin.page')
            ])->header('Content-Type', 'application/json');
        } catch(\Illuminate\Http\Exceptions\HttpResponseException $e){
            return response()->json([
                'type' => 'error',
                'title' => 'Oops...',
                'text' => 'Email atau password salah'
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'title' => 'Oops...',
                'text' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->header('Content-Type', 'application/json');
        }
    }

    public function banUser($id)
    {
        try {
            $result = $this->userRepository->bannedUser($id);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'User berhasil dibanned'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Gagal membanned user'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTerms()
    {
        try {
            $terms = $this->TermsRepository->showTerms();
            return response()->json([
                'success' => true,
                'data' => $terms
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data aturan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function addTerms(Request $request)
    {
        try {
            $data = $this->TermsRepository->addTerms([
                'name' => $request->name,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aturan berhasil ditambahkan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan aturan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function editTerms($id, Request $request)
    {
        try {
            $data = $this->TermsRepository->editTerns($id, [
                'name' => $request->name,
                'message' => $request->message
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Aturan berhasil diperbarui',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui aturan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeTerms($id)
    {
        try {
            $this->TermsRepository->removeTerms($id);
            return response()->json([
                'success' => true,
                'message' => 'Aturan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus aturan: ' . $e->getMessage()
            ], 500);
        }
    }
}