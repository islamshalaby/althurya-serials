<?php

namespace App\Imports;

use App\Serial;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SerialImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row['pin']);
        $request = request()->all();
		
        $serial = new Serial([
            "serial" => $row['pin'],
            "serial_number" => $row['sn'],
            "valid_to" => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']),
            "product_id" => $request['product_id']
        ]);
		
		return $serial;
    }
	
	public function rules(): array
{
    return [
        'pin' => 'required|string',
    ];
}

    // start from second row
    public function headingRow(): int
    {
        return 1;
    }
}
