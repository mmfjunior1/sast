<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserController extends Controller
{
    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function show($id)
    {
        return view('admin.users.index', ['user' => User::findOrFail($id)]);
    }
    /**
     * List all profiles.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', ['users' => $users]);
    }

    /**
     * Update the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function save(Request $request)
    {
        $input = $request->all();
        $users = new User();
        $input['codigo'] = (int) $input['codigo'];
        if ($input['codigo'] > 0) {
            $users = User::find($input['codigo']);
        }
        $users->nome = $input['nome'];
        $users->email = $input['email'];
        $users->telefone = $input['telefone'];
        $users->status = (int) $input['status'];
        $users->tipo = (int) $input['tipo'];
        $input['senha'] = trim($input['senha']);
        if ($input['senha'] != '') {
            $users->senha = md5($input['senha']);
        }
        $users->save();
        return redirect('usuarios')->with('success', 'Operação conluída');
    }

    /**
     * Delete the profile for the given user.
     *
     * @param  int  $id
     * @return redirect()
     */
    public function delete($id)
    {
        $id = (int) $id;
        $users = User::find($id);
        $users->delete();
        return redirect('usuarios');
    }

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function get($id)
    {
        $id = (int) $id;
        $user = User::find($id);
        $users = User::all();
        return view('admin.users.index', ['users' => $users, 'user' => User::findOrFail($id)]);
    }
}