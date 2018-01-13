<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use App\Filters\DocumentFilter;
use Verkoo\Common\Entities\Quote;

class QuotesController extends ApiController
{
    public function index()
    {
        $quotes = Quote::searchBetweenDates()
            ->searchByCustomer()
            ->searchBySerie()
            ->get();

        $quotes = (new DocumentFilter(request('type'), $quotes))->apply();

        return $this->respond($quotes);
    }

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
        ]);

        $quote = Quote::create(request()->all());

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $quote->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function update(Quote $quote)
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'cashed_amount' => 'numeric',
        ]);

        $quote->update(request()->all());

        $quote->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $quote->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();

        return $this->respond(['success' => true]);
    }
}
