<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class LookupEducationDetails extends Model {

	public function getOptions()
	{
		$courseLevel = LookupEducationDetails::groupBy('courseLevel')->lists('courseLevel');
		$courseSubject = LookupEducationDetails::groupBy('courseSubject')->lists('courseSubject');
		$country = LookupEducationDetails::groupBy('instituteCountry')->lists('instituteCountry');
		$instituteType = LookupEducationDetails::groupBy('instituteType')->lists('instituteType');
		$instituteRating = LookupEducationDetails::groupBy('instituteRating')->lists('instituteRating');
		return ['courseLevel' => $courseLevel, 'courseSubject' => $courseSubject, 'instituteCountry' => $country, 'instituteType' => $instituteType, 'instituteRating' => $instituteRating];
	}

	public function getRatings($input)
	{
		return LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->groupBy('instituteRating')->lists('instituteRating');
	}

	public function getRange($input)
	{
		if(isset($input['instituteRating'])){
			$min =  LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $input['instituteRating'])->min('instituteTotalCosts');
			$max =  LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $input['instituteRating'])->max('instituteTotalCosts');
			// check if inr or not and return based on the same.
			if(LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $input['instituteRating'])->groupBy('instituteCurrency')->select(array('instituteCurrency'))->count() > 0) {
				$currency = LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $input['instituteRating'])->groupBy('instituteCurrency')->select(array('instituteCurrency'))->get();
				if($currency[0]['instituteCurrency'] != "INR") {
					if(CurrencyMappings::where('currency_name',$currency[0]['instituteCurrency'])->count() > 0) {
						$exchange = CurrencyMappings::where('currency_name',$currency[0]['instituteCurrency'])->get();
						$min = $exchange[0]['exchange_rate'] * $min;
						$max = $exchange[0]['exchange_rate'] * $max;
					}
				}
			}

			return [$min, $max];
		}
		else{
			$ratings = ['1' => '1-Top Tier', '2' => '2-Middle Tier', '3' => '3-Others'];
			foreach ($ratings as $key => $rating) {
				$min =  LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $rating)->min('instituteTotalCosts');
				$max =  LookupEducationDetails::where('courseLevel', $input['courseLevel'])->where('courseSubject', $input['courseSubject'])->where('instituteCountry', $input['instituteCountry'])->where('instituteType', $input['instituteType'])->where('instituteRating', $rating)->max('instituteTotalCosts');
				$temp[$rating] = [$min, $max];
			}
			return $temp;
		}

	}

}
