<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'pdf' => ['required', 'mimes:pdf'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        if (request()->has('image') && request()->has('image')) {
            $requestPDF = request()->file('pdf');
            $filePDFWithExt = $requestPDF->getClientOriginalName();
            $file  = $requestPDF->storeAs('public/upload/', $filePDFWithExt);
            $requestImage = request()->file('image');
            $fileImageWithExt = $requestImage->getClientOriginalName();
            $image  = $requestImage->storeAs('public/upload/', $fileImageWithExt);

            /////////////////////////////////////////////////////////////////
            $pathPdf = Storage::disk('public')->getAdapter()->applyPathPrefix("upload\\".$filePDFWithExt);
            $pathImage = Storage::disk('public')->getAdapter()->applyPathPrefix("upload\\".$fileImageWithExt);
            // initiate FPDI
            $pdf = new Fpdi();
            $this->addImageToPDF($pdf ,$pathImage , $pathPdf);
            /////////////////////////////////////////////////////////////////////// 
            $user =  User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            $user->files()->createMany([
                [
                    'name' => $filePDFWithExt,
                    'url' => $pathPdf
                ],
                [
                    'name' => $fileImageWithExt,
                    'url' => $pathImage
                ],
            ]);
            return $user;
        }




    }
    public function addImageToPDF($pdf , $pathImage ,$pathPdf)
    {
            // add a page
            $pdf->AddPage();
            // set the source file
            $pdf->setSourceFile($pathPdf);
            // import page 1
            $tplId = $pdf->importPage(1);
            // use the imported page and place it at point 10,10 with a width of 100 mm
            $pdf->Image($pathImage,10,10,-300);
            $pdf->useTemplate($tplId, 10, 10, 100);
            $pdf->Output(storage_path('app/public/upload/merged.pdf') ,'F');

    }
}
