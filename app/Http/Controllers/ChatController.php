<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use App\Models\Transaction;

class ChatController extends Controller
{
    public function index()
    {
        // Note: Task expiration and fines are now handled by the Scheduler in routes/console.php

        $messages = Message::with('sender')->orderBy('created_at', 'desc')->take(50)->get()->reverse();
        $users = User::where('id', '!=', auth()->id())->get(); // For task assignment dropdown
        
        if (auth()->user()->role === 'admin') {
            $tasks = Task::with(['assigner', 'assignee'])->orderBy('created_at', 'desc')->get();
        } else {
            $tasks = Task::with(['assigner'])->where('assigned_to', auth()->id())->orderBy('created_at', 'desc')->get();
        }

        return view('dashboards.chat', compact('messages', 'users', 'tasks'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:20480'
        ]);

        if (!$request->message && !$request->hasFile('attachment')) {
            return redirect()->back()->withErrors('Message or file is required.');
        }

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('chat_files', 'public');
        }

        Message::create([
            'sender_id' => auth()->id(),
            'message' => $request->message ?? '',
            'file_path' => $filePath
        ]);

        return redirect()->back();
    }
    
    public function assignTask(Request $request)
    {
        
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date|after:now',
            'fine_amount' => 'required|numeric|min:0',
            'xp_reward' => 'required|integer|min:0',
        ]);
        
        $task = Task::create([
            'assigned_by' => auth()->id(),
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'fine_amount' => $request->fine_amount,
            'xp_reward' => $request->xp_reward,
        ]);
        
        $user = User::find($request->assigned_to);
        
        // Notify in Global Chat
        Message::create([
            'sender_id' => auth()->id(),
            'message' => "NEW DIRECTIVE ASSIGNED TO {$user->name}: {$task->title}. Deadline: {$task->deadline}. Failure Penalty: {$task->fine_amount} UZS."
        ]);
        
        return redirect()->back()->with('success', 'Directive assigned successfully.');
    }
    
    public function completeTask(Task $task)
    {
        if ($task->assigned_to !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
        
        if ($task->status !== 'pending') {
            return redirect()->back()->with('error', 'Task is no longer active.');
        }
        
        $task->update(['status' => 'done']);
        
        if ($task->xp_reward > 0) {
            $user = User::find($task->assigned_to);
            if ($user) {
                $user->xp += $task->xp_reward;
                $user->save();
            }
        }
        
        Message::create([
            'sender_id' => auth()->id(),
            'message' => "DIRECTIVE COMPLETED: '{$task->title}' has been marked as DONE."
        ]);
        
        return redirect()->back()->with('success', 'Directive marked as completed.');
    }

    public function clear()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        Message::truncate();
        return redirect()->back()->with('success', 'Chat tarixi tozalandi.');
    }

    public function deleteMessage(Message $message)
    {
        if (auth()->user()->role !== 'admin' && $message->sender_id !== auth()->id()) abort(403);
        $message->delete();
        return redirect()->back()->with('success', 'Xabar o\'chirildi.');
    }

    public function editMessage(Request $request, Message $message)
    {
        if (auth()->user()->role !== 'admin' && $message->sender_id !== auth()->id()) abort(403);
        
        $request->validate([
            'message' => 'required|string',
        ]);
        
        $message->update(['message' => $request->message . ' (tahrirlandi)']);
        return redirect()->back()->with('success', 'Xabar tahrirlandi.');
    }

    public function deleteTask(Task $task)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $task->delete();
        return redirect()->back()->with('success', 'Vazifa o\'chirildi.');
    }

    public function editTask(Request $request, Task $task)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'required|date',
            'fine_amount' => 'required|numeric|min:0',
            'xp_reward' => 'required|integer|min:0',
        ]);
        
        $task->update([
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'fine_amount' => $request->fine_amount,
            'xp_reward' => $request->xp_reward,
        ]);
        
        return redirect()->back()->with('success', 'Vazifa tahrirlandi.');
    }
}
