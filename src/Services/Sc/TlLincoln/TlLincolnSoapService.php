<?php

namespace ThachVd\LaravelSiteControllerApi\Services\Sc\TlLincoln;

use ThachVd\LaravelSiteControllerApi\Models\ScTlLincolnSoapApiLog;
use ThachVd\LaravelSiteControllerApi\Models\TllincolnAccount;
use ThachVd\LaravelSiteControllerApi\Services\Sc\Xml2Array\Xml2Array;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

/**
 *
 */
class TlLincolnSoapService
{
    /**
     * @var TlLincolnSoapClient
     */
    protected $tlLincolnSoapClient;
    /**
     * @var TlLincolnSoapBody
     */
    protected $tlLincolnSoapBody;

    /**
     * @param TlLincolnSoapClient $tlLincolnSoapClient
     * @param TlLincolnSoapBody $tlLincolnSoapBody
     */
    public function __construct(TlLincolnSoapClient $tlLincolnSoapClient, TlLincolnSoapBody $tlLincolnSoapBody)
    {
        $this->tlLincolnSoapClient = $tlLincolnSoapClient;
        $this->tlLincolnSoapBody   = $tlLincolnSoapBody;
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getEmptyRoom(Request $request)
    {
        $isWriteLog     = config('sc.is_write_log');
        $dateValidation = $this->validateAndParseDates($request);
        if (isset($dateValidation['success']) && !$dateValidation['success']) {
            return $dateValidation;
        }

        $command = 'roomAvailabilitySalesSts';
        // set header request
        $this->tlLincolnSoapClient->setHeaders();
        // set body request
        $this->setEmptyRoomSoapRequest($dateValidation, $request);

        try {
            $url        = config('sc.tllincoln_api.get_empty_room_url');
            $soapApiLog = [
                'data_id' => ScTlLincolnSoapApiLog::genDataId(),
                'url'     => $url,
                'command' => $command,
                "request" => $this->tlLincolnSoapClient->getBody(),
            ];
            $response   = $this->tlLincolnSoapClient->callSoapApi($url);

            $data    = [];
            $success = true;

            if ($response !== null) {
                $rooms = $this->tlLincolnSoapClient->convertResponeToArray($response);

                if (isset($rooms['ns2:roomAvailabilitySalesStsResponse']['roomAvailabilitySalesStsResult']['hotelInfos'])) {
                    $data = $rooms['ns2:roomAvailabilitySalesStsResponse']['roomAvailabilitySalesStsResult']['hotelInfos'];
                }
            } else {
                $success = false;
            }
            if ($isWriteLog) {
                $soapApiLog['response']   = $response;
                $soapApiLog['is_success'] = $success;
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success'     => $success,
                'data'        => $data,
                'xmlResponse' => $response
            ]);
        } catch (\Exception $e) {
            if ($isWriteLog) {
                $soapApiLog['response']   = $e->getMessage();
                $soapApiLog['is_success'] = false;
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBulkEmptyRoom(Request $request)
    {
        $command = 'roomAvailabilityAllSalesSts';
        // set header request
        $this->tlLincolnSoapClient->setHeaders();
        // set body request
        $this->setBulkEmptyRoomSoapRequest($request);
        try {
            $url        = config('sc.tllincoln_api.get_empty_room_series_url');
            $soapApiLog = [
                'data_id' => ScTlLincolnSoapApiLog::genDataId(),
                'url'     => $url,
                'command' => $command,
                "request" => $this->tlLincolnSoapClient->getBody(),
            ];

            $response = $this->tlLincolnSoapClient->callSoapApi($url);

            $data    = [];
            $success = true;

            if ($response !== null) {
                $arrRooms = $this->tlLincolnSoapClient->convertResponeToArray($response);

                if (isset($arrRooms['ns2:roomAvailabilityAllSalesStsResponse']['roomAvailabilityAllSalesStsResult']['hotelInfos'])) {
                    $data = $arrRooms['ns2:roomAvailabilityAllSalesStsResponse']['roomAvailabilityAllSalesStsResult']['hotelInfos'];
                }
            } else {
                $success = false;
            }

            $soapApiLog['response']   = $response;
            $soapApiLog['is_success'] = $success;
            ScTlLincolnSoapApiLog::createLog($soapApiLog);

            return response()->json([
                'success' => $success,
                'data'    => $data,
                'date'    => now()->format(config('sc.tllincoln_api.date_format')),
            ]);
        } catch (\Exception $e) {
            $soapApiLog['response']   = $e->getMessage();
            $soapApiLog['is_success'] = false;
            ScTlLincolnSoapApiLog::createLog($soapApiLog);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getPricePlan(Request $request)
    {
        $isWriteLog     = config('sc.is_write_log');
        $dateValidation = $this->validateAndParseDates($request);
        if (isset($dateValidation['success']) && !$dateValidation['success']) {
            return $dateValidation;
        }

        $command = 'planPriceInfoAcquisition';
        // set header request
        $this->tlLincolnSoapClient->setHeaders();
        // set body request
        $this->setPricePlanSoapRequest($dateValidation, $request);

        try {
            $url        = config('sc.tllincoln_api.get_plan_price_url');
            $soapApiLog = [
                'data_id' => ScTlLincolnSoapApiLog::genDataId(),
                'url'     => $url,
                'command' => $command,
                "request" => $this->tlLincolnSoapClient->getBody(),
            ];
            $response   = $this->tlLincolnSoapClient->callSoapApi($url);
            $data       = [];
            $success    = true;

            if ($response !== null) {
                $arrPlans = $this->tlLincolnSoapClient->convertResponeToArray($response);
                if (isset($arrPlans['ns2:planPriceInfoAcquisitionResponse']['planPriceInfoResult']['hotelInfos'])) {
                    $data = $arrPlans['ns2:planPriceInfoAcquisitionResponse']['planPriceInfoResult']['hotelInfos'];
                }
            } else {
                $success = false;
            }

            if ($isWriteLog) {
                $soapApiLog['response']   = $response;
                $soapApiLog['is_success'] = $success;
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success'     => $success,
                'data'        => $data,
                'xmlResponse' => $response
            ]);
        } catch (\Exception $e) {
            if ($isWriteLog) {
                $soapApiLog['response']   = $e->getMessage();
                $soapApiLog['is_success'] = false;
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBulkPricePlan(Request $request)
    {
        $command = 'planPriceInfoAcquisitionAll';
        // set header request
        $this->tlLincolnSoapClient->setHeaders();
        // set body request
        $this->setBulkPricePlanSoapRequest($request);

        try {
            $url        = config('sc.tllincoln_api.get_plan_price_series_url');
            $soapApiLog = [
                'data_id' => ScTlLincolnSoapApiLog::genDataId(),
                'url'     => $url,
                'command' => $command,
                "request" => $this->tlLincolnSoapClient->getBody(),
            ];
            $response   = $this->tlLincolnSoapClient->callSoapApi($url);
            $data       = [];
            $success    = true;

            if ($response !== null) {
                $arrPrices = $this->tlLincolnSoapClient->convertResponeToArray($response);
                if (isset($arrPrices['ns2:planPriceInfoAcquisitionAllResponse']['planPriceInfoAllResult']['hotelInfos'])) {
                    $data = $arrPrices['ns2:planPriceInfoAcquisitionAllResponse']['planPriceInfoAllResult']['hotelInfos'];
                }
            } else {
                $success = false;
            }

            $soapApiLog['response']   = $response;
            $soapApiLog['is_success'] = $success;
            ScTlLincolnSoapApiLog::createLog($soapApiLog);

            return response()->json([
                'success' => $success,
                'data'    => $data,
                'date'    => now()->format(config('sc.tllincoln_api.date_format')),
            ]);
        } catch (\Exception $e) {
            $soapApiLog['response']   = $e->getMessage();
            $soapApiLog['is_success'] = false;
            ScTlLincolnSoapApiLog::createLog($soapApiLog);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    public function createBooking(Request $request)
    {
        // precheck create booking
        $preCheckBookingResponse = $this->preCheckCreateBooking($request);
        //TODO check preCheckBookingResponse success

        // entry booking
        $entryBookingResponse = $this->entryBooking($request);
        // TODO check entryBookingResponse success
        // TODO return response to client
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function preCheckCreateBooking($request)
    {
        $url     = config('sc.tllincoln_api.check_pre_booking_url');
        $command = 'preBooking';

        $response = $this->processBooking($url, $command, $request);

        return $response;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function entryBooking($request)
    {
        $url     = config('sc.tllincoln_api.entry_booking_url');
        $command = 'entryBooking';

        $response = $this->processBooking($url, $command, $request);

        return $response;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function cancelBooking($request)
    {
        $url     = config('sc.tllincoln_api.cancel_booking_url');
        $command = 'deleteBookingWithCP';

        $response = $this->processBooking($url, $command, $request);

        return $response;
    }

    /**
     * Prepare TLL Soap Body
     *
     * @param $command
     * @param array $dataRequest
     * @return void
     */
    public function prepareTllSoapBody($command, $dataRequest)
    {
        $tllincolnAccount = TllincolnAccount::first();
        $userInfo         = [
            'agtId'       => $tllincolnAccount->agt_id,
            'agtPassword' => $tllincolnAccount->agt_password
        ];
        $this->tlLincolnSoapBody->setMainBodyWrapSection($command . 'Request');
        $xmlnsType       = config('sc.tllincoln_api.xml.xmlns_type');
        $xmlnsVersionKey = "sc.tllincoln_api.xml.$xmlnsType.$xmlnsType" . '_booking';
        $xmlnsVersion    = config($xmlnsVersionKey);
        $body            = $this->tlLincolnSoapBody->generateBody(
            $command,
            $dataRequest,
            $xmlnsType,
            $xmlnsVersion,
            $userInfo
        );
        $this->tlLincolnSoapClient->setHeaders();
        $this->tlLincolnSoapClient->setBody($body);
    }

    /**
     * @param $url
     * @param $command
     * @param $request
     * @return mixed
     */
    public function processBooking($url, $command, $request)
    {
        $this->prepareTllSoapBody($command, $request);
        try {
            $isWriteLog = config('sc.is_write_log');
            $soapApiLog = [
                'data_id' => ScTlLincolnSoapApiLog::genDataId(),
                'url'     => $url,
                'command' => $command,
                "request" => $this->tlLincolnSoapClient->getBody(),
            ];
            $response   = $this->tlLincolnSoapClient->callSoapApi($url);
            $data       = [];
            $success    = true;
            $message    = ["TL エラー:"];

            if ($response !== null) {
                $data           = Xml2Array::toArray($response);
                $commonResponse = $data['S:Body']['ns2:' . $command . 'Response'][$command . 'Result']['commonResponse'];
                $success        = $commonResponse['resultCode'] === "True";

                if (!$success) {
                    if (isset($commonResponse['errorInfos']['errorMsg'])) {
                        $message[] = $commonResponse['errorInfos']['errorMsg'];
                        \Log::info("Meet TLL Error Code {$commonResponse['errorInfos']['errorCode']}");
                        $data["errorCode"] = $commonResponse['errorInfos']['errorCode'];
                    } else {
                        foreach ($commonResponse['errorInfos'] as $error) {
                            $message[] = $error['errorMsg'];
                        }
                    }
                } else {
                    // handle response data
                    if (isset($data['S:Body']['ns2:' . $command . 'Response'][$command . 'Result']['extendLincoln'])) {
                        $data = $data['S:Body']['ns2:' . $command . 'Response'][$command . 'Result']['extendLincoln'];
                    }
                }
            } else {
                $success = false;
            }
            $soapApiLog['response']   = $response;
            $soapApiLog['is_success'] = $success;
            if ($isWriteLog) {
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success'     => $success,
                'message'     => $success ? [] : $message,
                'data'        => $data,
                'xmlResponse' => $response
            ]);
        } catch (\Exception $e) {
            $soapApiLog['response']   = $e->getMessage();
            $soapApiLog['is_success'] = false;
            if ($isWriteLog) {
                ScTlLincolnSoapApiLog::createLog($soapApiLog);
            }

            return response()->json([
                'success'     => false,
                'message'     => $e->getMessage(),
                'xmlResponse' => $response
            ]);
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    private function validateAndParseDates(Request $request)
    {
        $dateFormat = config('sc.tllincoln_api.tllincoln_date_format_api');
        $dateFrom   = $request->input('date_from');
        $dateTo     = $request->input('date_to');
        try {
            $startDay  = $dateFrom ? Carbon::parse($dateFrom)->format($dateFormat) : Carbon::now()->format($dateFormat);
            $endDay    = $dateTo ? Carbon::parse($dateTo)->format($dateFormat) : Carbon::parse($startDay)->addDay(
                config('sc.tllincoln_api.get_empty_room_max_day')
            )->format($dateFormat);
            $endDayMax = Carbon::parse($startDay)->addDays(30)->format($dateFormat);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'date input is not valid!'
            ];
        }

        $validator = \Validator::make($request->all(), [
            'date_from' => ['nullable', 'date', 'date_format:' . $dateFormat],
            'date_to'   => [
                'nullable',
                'date',
                'date_format:' . $dateFormat,
                'after_or_equal:date_from',
                'before_or_equal:' . $endDayMax
            ]
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()
            ];
        }

        return compact('startDay', 'endDay');
    }

    /**
     * @param array $dateValidation
     * @param Request $request
     * @return void
     */
    public function setEmptyRoomSoapRequest(array $dateValidation, Request $request): void
    {
        $startDay       = $dateValidation['startDay'];
        $endDay         = $dateValidation['endDay'];
        $perRmPaxCount  = $request->input('person_number');
        $tllHotelCode   = $request->input('tllHotelCode');
        $tllRmTypeCode  = $request->input('tllRmTypeCode');
        $tllRmTypeInfos = [];

        if (!is_array($tllRmTypeCode)) {
            $tllRmTypeInfos['tllRmTypeCode'] = $tllRmTypeCode;
        } else {
            foreach ($tllRmTypeCode as $item) {
                $tllRmTypeInfos[] = ['tllRmTypeCode' => $item];
            }
        }

        $dataRequest = [
            'extractionRequest' => [
                'startDay'      => $startDay,
                'endDay'        => $endDay,
                'perRmPaxCount' => $perRmPaxCount,
            ],
            'hotelInfos'        => [
                'tllHotelCode'   => $tllHotelCode,
                'tllRmTypeInfos' => $tllRmTypeInfos
            ]
        ];

        $this->tlLincolnSoapBody->setMainBodyWrapSection('roomAvailabilitySalesStsRequest');
        $tllincolnAccount = TllincolnAccount::first();
        $userInfo         = [
            'agtId'       => $tllincolnAccount->agt_id,
            'agtPassword' => $tllincolnAccount->agt_password
        ];
        $xmlnsType        = config('sc.tllincoln_api.xml.xmlns_type');
        $xmlnsVersionKey  = "sc.tllincoln_api.xml.$xmlnsType.$xmlnsType" . '_common';
        $xmlnsVersion     = config($xmlnsVersionKey);
        $body             = $this->tlLincolnSoapBody->generateBody(
            'roomAvailabilitySalesSts',
            $dataRequest,
            $xmlnsType,
            $xmlnsVersion,
            $userInfo
        );
        $this->tlLincolnSoapClient->setBody($body);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function setBulkEmptyRoomSoapRequest(Request $request): void
    {
        $tllHotelCode   = $request->input('tllHotelCode');
        $tllRmTypeCode  = $request->input('tllRmTypeCode');
        $tllRmTypeInfos = [];

        if (!is_array($tllRmTypeCode)) {
            $tllRmTypeInfos['tllRmTypeCode'] = $tllRmTypeCode;
        } else {
            foreach ($tllRmTypeCode as $item) {
                $tllRmTypeInfos[] = ['tllRmTypeCode' => $item];
            }
        }

        $dataRequest = [
            'hotelInfos' => [
                'tllHotelCode'   => $tllHotelCode,
                'tllRmTypeInfos' => $tllRmTypeInfos
            ]
        ];

        $this->tlLincolnSoapBody->setMainBodyWrapSection('roomAvailabilityAllSalesStsRequest');
        $tllincolnAccount = TllincolnAccount::first();
        $userInfo         = [
            'agtId'       => $tllincolnAccount->agt_id,
            'agtPassword' => $tllincolnAccount->agt_password
        ];
        $xmlnsType        = config('sc.tllincoln_api.xml.xmlns_type');
        $xmlnsVersionKey  = "sc.tllincoln_api.xml.$xmlnsType.$xmlnsType" . '_common';
        $xmlnsVersion     = config($xmlnsVersionKey);

        $body = $this->tlLincolnSoapBody->generateBody(
            'roomAvailabilityAllSalesSts',
            $dataRequest,
            $xmlnsType,
            $xmlnsVersion,
            $userInfo
        );
        $this->tlLincolnSoapClient->setBody($body);
    }

    /**
     * @param array $dateValidation
     * @param Request $request
     * @return void
     */
    public function setPricePlanSoapRequest(array $dateValidation, Request $request): void
    {
        $startDay      = $dateValidation['startDay'];
        $endDay        = $dateValidation['endDay'];
        $minPrice      = $request->input('min_price');
        $maxPrice      = $request->input('max_price');
        $perPaxCount   = $request->input('person_number');
        $tllHotelCode  = $request->input('tllHotelCode');
        $tllRmTypeCode = $request->input('tllRmTypeCode');
        $tllPlanCode   = $request->input('tllPlanCode');
        $tllPlanInfos  = [];
        if (!is_array($tllPlanCode)) {
            $tllPlanInfos['tllPlanCode'] = $tllPlanCode;
        } else {
            foreach ($tllPlanCode as $item) {
                $tllPlanInfos[] = ['tllPlanCode' => $item];
            }
        }
        if (!is_array($tllRmTypeCode)) {
            $tllPlanInfos['tllRmTypeCode'] = $tllRmTypeCode;
        } else {
            foreach ($tllRmTypeCode as $item) {
                $tllPlanInfos[] = ['tllRmTypeCode' => $item];
            }
        }

        $dataRequest = [
            'extractionRequest' => [
                'startDay'    => $startDay,
                'endDay'      => $endDay,
                'minPrice'    => $minPrice,
                'maxPrice'    => $maxPrice,
                'perPaxCount' => $perPaxCount
            ],
            'hotelInfos'        => [
                'tllHotelCode' => $tllHotelCode,
                'tllPlanInfos' => $tllPlanInfos
            ]
        ];


        $this->tlLincolnSoapBody->setMainBodyWrapSection('planPriceInfoAcquisitionRequest');
        $tllincolnAccount = TllincolnAccount::first();
        $userInfo         = [
            'agtId'       => $tllincolnAccount->agt_id,
            'agtPassword' => $tllincolnAccount->agt_password
        ];
        $xmlnsType        = config('sc.tllincoln_api.xml.xmlns_type');
        $xmlnsVersionKey  = "sc.tllincoln_api.xml.$xmlnsType.$xmlnsType" . '_common';
        $xmlnsVersion     = config($xmlnsVersionKey);
        $body             = $this->tlLincolnSoapBody->generateBody(
            'planPriceInfoAcquisition',
            $dataRequest,
            $xmlnsType,
            $xmlnsVersion,
            $userInfo
        );
        $this->tlLincolnSoapClient->setBody($body);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function setBulkPricePlanSoapRequest(Request $request): void
    {
        $tllHotelCode  = $request->input('tllHotelCode');
        $tllRmTypeCode = $request->input('tllRmTypeCode');
        $tllPlanCode   = $request->input('tllPlanCode');
        $dateNow       = Carbon::now();

        $tllRmTypeInfos = [];
        if (!is_array($tllRmTypeCode)) {
            $tllRmTypeInfos['tllRmTypeCode'] = $tllRmTypeCode;
            $tllRmTypeInfos['tllPlanCode']   = $tllPlanCode;
        } else {
            foreach ($tllRmTypeCode as $item) {
                $tllRmTypeInfos[] = ['tllRmTypeCode' => $item, 'tllPlanCode' => $tllPlanCode];
            }
        }

        $dataRequest = [
            'hotelInfos' => [
                'tllHotelCode' => $tllHotelCode,
                'tllPlanInfos' => $tllRmTypeInfos
            ]
        ];


        $this->tlLincolnSoapBody->setMainBodyWrapSection('planPriceInfoAcquisitionAllRequest');
        $tllincolnAccount = TllincolnAccount::first();
        $userInfo         = [
            'agtId'       => $tllincolnAccount->agt_id,
            'agtPassword' => $tllincolnAccount->agt_password
        ];
        $xmlnsType        = config('sc.tllincoln_api.xml.xmlns_type');
        $xmlnsVersionKey  = "sc.tllincoln_api.xml.$xmlnsType.$xmlnsType" . '_common';
        $xmlnsVersion     = config($xmlnsVersionKey);
        $body             = $this->tlLincolnSoapBody->generateBody(
            'planPriceInfoAcquisitionAll',
            $dataRequest,
            $xmlnsType,
            $xmlnsVersion,
            $userInfo
        );
        $this->tlLincolnSoapClient->setBody($body);
    }
}
