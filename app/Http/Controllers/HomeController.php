<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\FormPrefillHelper;

use App\Models\User;
use App\Models\Form;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    // In controller, redirect with prefill
    public function redirectToForm($userId) 
    {
        $form = Form::findOrFail(1);

        $user = User::find($userId);
        
        $prefillData = [
            'employee_name' => $user->name,
            'department' => $user->departments->first()?->code,
            'employee_id' => $user->id,
            'current_date' => now()->format('Y-m-d')
        ];
        
        $prefillUrl = FormPrefillHelper::generatePrefillUrl($form, $prefillData);
        
        return redirect($prefillUrl);
        /*
        // Basic prefill
/formsubmissions/form/1?employee_name=John Doe&department=HR&salary=5000000

// Date prefill  
/formsubmissions/form/1?start_date=2024-01-15&end_date=2024-01-20

// Multiple select prefill
/formsubmissions/form/1?skills=php,laravel,javascript&departments=HR,IT

// Boolean prefill
/formsubmissions/form/1?is_permanent=true&has_experience=1

// Mixed prefill
/formsubmissions/form/1?name=Ahmad&age=25&department=IT&salary=8000000&start_date=2024-02-01&is_active=true
*/
    }
}
