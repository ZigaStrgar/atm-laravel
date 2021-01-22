<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    public function show(Request $request)
    {
        $from = $request->has('from') ? Carbon::parse($request->get('from'))->startOfDay() : now()->subDays(7)->startOfDay();
        $to = $request->has('to') ? Carbon::parse($request->get('to'))->endOfDay() : now()->endOfDay();

        //        SELECT
        //        	t.country AS country,
        //        	DATE(t.created_at) AS creation_date,
        //        	COUNT(DISTINCT (t.user_id)) AS customers,
        //            (SELECT COUNT(tn.id) FROM transactions tn WHERE tn.type = "deposit" AND DATE(tn.created_at) = creation_date AND tn.country = t.country) AS number_of_deposits,
        //            (SELECT SUM(tn.amount) FROM transactions tn WHERE tn.type = "deposit" AND DATE(tn.created_at) = creation_date AND tn.country = t.country) AS total_deposits,
        //            (SELECT COUNT(tn.id) FROM transactions tn WHERE tn.type = "withdraw" AND DATE(tn.created_at) = creation_date AND tn.country = t.country) AS number_of_withdraws,
        //            (SELECT SUM(tn.amount) FROM transactions tn WHERE tn.type = "withdraw" AND DATE(tn.created_at) = creation_date AND tn.country = t.country) AS total_withdraws
        //        FROM
        //        	transactions t
        //        WHERE
        //            DATE(created_at) BETWEEN ? AND ?
        //        GROUP BY
        //        	creation_date,
        //        	t.country;

        $report = Transaction::addSelect(['country', DB::raw('DATE(created_at) as `creation_date`'), DB::raw('COUNT(DISTINCT(user_id)) as `unique_customers`')])
            ->addSelect(['total_deposits' => Transaction::from(DB::raw('transactions as t'))->whereBetween('created_at', [$from, $to])->whereType('deposit')->whereColumn('country', 'transactions.country')->select(DB::raw('SUM(amount)'))])
            ->addSelect(['total_withdraws' => Transaction::from(DB::raw('transactions as t'))->whereBetween('created_at', [$from, $to])->whereType('withdraw')->whereColumn('country', 'transactions.country')->select(DB::raw('SUM(amount)'))])
            ->addSelect(['number_of_withdraws' => Transaction::from(DB::raw('transactions as t'))->whereBetween('created_at', [$from, $to])->whereType('withdraw')->whereColumn('country', 'transactions.country')->select(DB::raw('COUNT(id)'))])
            ->addSelect(['number_of_deposits' => Transaction::from(DB::raw('transactions as t'))->whereBetween('created_at', [$from, $to])->whereType('deposit')->whereColumn('country', 'transactions.country')->select(DB::raw('COUNT(id)'))])
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('creation_date', 'country')
            ->get();

        return $report;
    }

    public function showCollections(Request $request)
    {
        $from = $request->has('from') ? Carbon::parse($request->get('from'))->startOfDay() : now()->subDays(7)->startOfDay();
        $to = $request->has('to') ? Carbon::parse($request->get('to'))->endOfDay() : now()->endOfDay();

        $transactions = Transaction::addSelect(DB::raw('*, DATE(created_at) as created_date'))->whereBetween('created_at', [$from, $to])->get();

        return $transactions->groupBy('created_date')->map(function ($dateGroup, $date) {
            return $dateGroup->groupBy('country')->map(function ($records, $country) use ($date) {
                [$deposits, $withdraws] = $records->partition(function ($record) {
                    return $record['type'] === 'deposit';
                });

                return [
                    'creation_date' => $date,
                    'country' => $country,
                    'unique_customers' => $records->pluck('user_id')->unique()->count(),
                    'number_of_deposits' => $deposits->count(),
                    'total_deposits' => $deposits->sum('amount'),
                    'number_of_withdraws' => $withdraws->count(),
                    'total_withdraws' => $withdraws->sum('amount'),
                ];
            })->values();
        })->collapse();
    }
}
