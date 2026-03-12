<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiAssistantController extends Controller
{
    public function chat(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $msg = strtolower($request->message);
        
        // Data analysis
        $totalTreasury = Transaction::where('type', 'income')->sum('amount') - Transaction::where('type', 'expense')->sum('amount');
        $dailyIncome = Transaction::where('type', 'income')->whereDate('created_at', today())->sum('amount');
        $dailyExpense = Transaction::where('type', 'expense')->whereDate('created_at', today())->sum('amount');
        
        $topOperator = User::where('role', 'operator')
            ->withCount(['contracts' => function($q) { $q->whereDate('created_at', today())->where('status', 'approved'); }])
            ->orderBy('contracts_count', 'desc')
            ->first();
            
        $mostPopularService = DB::table('contracts')
            ->join('services', 'contracts.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('count(*) as total'))
            ->whereDate('contracts.created_at', today())
            ->groupBy('services.name')
            ->orderBy('total', 'desc')
            ->first();

        $pendingCount = Contract::where('status', 'pending')->count();
        $activeOperators = User::where('role', 'operator')->where('status', 'online')->count();

        // Analytical Logic
        $reply = "Men barcha tizim ma'lumotlarini tahlil qildim. Hozirda g'azna balansida " . number_format($totalTreasury, 0, '.', ' ') . " so'm mavjud. ";

        if (str_contains($msg, 'analitika') || str_contains($msg, 'tahlil') || str_contains($msg, 'vaziyat')) {
            $reply .= "Bugungi analitika: Jami kirim " . number_format($dailyIncome, 0, '.', ' ') . " so'm, chiqim esa " . number_format($dailyExpense, 0, '.', ' ') . " so'mni tashkil etmoqda. ";
            if ($topOperator && $topOperator->contracts_count > 0) {
                 $reply .= "Eng faol operator - " . $topOperator->name . " (" . $topOperator->contracts_count . " ta shartnoma). ";
            }
            if ($mostPopularService) {
                 $reply .= "Eng talabgir xizmat - " . $mostPopularService->name . ". ";
            }
            if ($pendingCount > 0) {
                 $reply .= "Diqqat: " . $pendingCount . " ta shartnoma kassir tomonidan tasdiqlanishini kutmoqda.";
            } else {
                 $reply .= "Barcha shartnomalar yakunlangan, navbatda hech kim yo'q.";
            }
        } 
        elseif (str_contains($msg, 'operator') || str_contains($msg, 'kim ishladi') || str_contains($msg, 'xodim')) {
            $reply = "Hozirda " . $activeOperators . " ta operator onlayn rejimda ishlamoqda. ";
            if ($topOperator && $topOperator->contracts_count > 0) {
                $reply .= "Bugun " . $topOperator->name . " eng ko'p natija ko'rsatib, " . number_format($topOperator->contracts_count) . " ta mijozga xizmat ko'rsatdi.";
            } else {
                $reply .= "Bugun hali operatorlar tomonidan shartnomalar yakunlanmagan.";
            }
        }
        elseif (str_contains($msg, 'foyda') || str_contains($msg, 'daromad') || str_contains($msg, 'pul')) {
            $efficiency = ($dailyIncome > 0) ? round((($dailyIncome - $dailyExpense) / $dailyIncome) * 100, 1) : 0;
            $reply = "Bugungi sof foyda " . number_format($dailyIncome - $dailyExpense, 0, '.', ' ') . " so'm. ";
            $reply .= "Rentabellik ko'rsatkichi: " . $efficiency . "%. ";
            if ($dailyExpense > $dailyIncome / 2) {
                $reply .= "Diqqat! Chiqimlar daromadga nisbatan yuqori, xarajatlarni kamaytirishni tavsiya qilaman.";
            } else {
                $reply .= "Moliyaviy ko'rsatkichlar barqaror darajada.";
            }
        }
        elseif (str_contains($msg, 'salom') || str_contains($msg, 'assalom')) {
            $reply = "Assalomu alaykum Admin! Men sizning neural tahlilchingizman. Tizimdagi moliyaviy, xodimlar va operatsion holat bo'yicha har qanday savolingizga javob bera olaman. Analitika so'raysizmi?";
        }
        else {
            $reply .= "Savolingizni tushunishga harakat qilyapman. Analitika, foyda yoki operatorlar haqida so'rasangiz, batafsil ma'lumot bera olaman.";
        }

        return response()->json(['reply' => $reply]);
    }
}
