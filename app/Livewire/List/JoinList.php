<?php

namespace App\Livewire\List;

use App\Actions\Sharing\RedeemShareCode;
use App\Exceptions\InvalidShareCodeException;
use App\Http\Requests\JoinListRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Lets a logged-in user join a shared list by entering (or following a link
 * containing) a share code.
 */
#[Layout('layouts.app')]
class JoinList extends Component
{
    public string $code = '';

    public function mount(?string $code = null)
    {
        if ($code !== null) {
            $this->code = strtoupper(trim($code));

            return $this->join();
        }
    }

    public function join()
    {
        $this->code = strtoupper(trim($this->code));

        $request = new JoinListRequest;

        $validator = Validator::make(
            ['code' => $this->code],
            $request->rules(),
            $request->messages(),
        );

        // Not $validator->validate(): a malformed code can arrive via the URL
        // (mount), where a thrown ValidationException would redirect away
        // instead of rendering the form with the error.
        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());

            return;
        }

        try {
            $list = app(RedeemShareCode::class)->handle($this->code, Auth::user());
        } catch (InvalidShareCodeException $e) {
            $this->addError('code', $e->getMessage());

            return;
        }

        session()->flash('message', "You've joined \"{$list->title}\". Time to vote!");

        return redirect()->route('lists.vote', ['list' => $list]);
    }

    public function render()
    {
        return view('livewire.list.join-list');
    }
}
