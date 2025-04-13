<?php

namespace LaravelDynamicApi\Traits;

use Carbon\Carbon;
use LaravelDynamicApi\Models\FailedRequest;
use LaravelDynamicApi\Models\Request;
use LaravelDynamicApi\Models\UserOnline;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/**
 * Engine execution functions
 * 
 * @since 24.03.2024
 * @author Pedro Domingos <pedro@panttera.com>
 */
trait EngineRequestFunctions
{
    /** Save request and the user.
     * 
     * 
     * @since 25.03.2024
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    public function saveRequest($request)
    {
        if (config('laravel-dynamic-api.track_requests', true)) {
            $requestData = [
                'track_id' => Str::orderedUuid(),
                'method' => $request->method(),
                'path' => $request->path(),
                'schema' => $request->schemeAndHttpHost(),
                'query' => $request->query(),
                'headers' => $request->header(),
                'request' => $request->all(),
                'ip' => $request->ip(),
            ];
            $this->userRequest = Request::create($requestData);
            $request->merge(["request_user_track_code" => $this->userRequest->track_id]);
        }
    }

    /** Save request and the user.
     * 
     * @since 25.03.2024
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    public function updateRequest($status = null, $returnOutput = null)
    {
        if (config('laravel-dynamic-api.track_requests', true)) {
            if ($this->userRequest) {
                $output = null;
                try {
                    $output = collect($returnOutput);
                } catch (Exception $e) {
                    $this->saveFaildedRequest();
                    $status = 500;
                    $output = $e->getMessage() . ' in file ' . $e->getFile() . ' on line ' . $e->getLine();
                }
                $this->userRequest->update([
                    'user_id' => $this->authUser ? $this->authUser->id : null,
                    'type' => $this->type,
                    'model_name' => $this->modelName,
                    'model_id' => $this->modelId,
                    'relation_name' => $this->relationName,
                    'relation_model_id' => $this->relationModelId,
                    'status' => $status,
                    'return_output' => $output,
                ]);
            }
        }
        if ($this->authUser) {
            $yesterday = Carbon::now()->setHours(0)->setMinutes(0)->setSeconds(0);
            if ($this->authUser->last_login_at < $yesterday) {
                // UserOnline::create(['user_id' => $this->authUser->id, 'date' => Carbon::now()]);
            }
            $this->authUser->update(['last_login_at' => Carbon::now()]);
        }
    }

    /** Save failed request.
     * 
     * @since 25.03.2024
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    public function saveFaildedRequest()
    {
        if (config('laravel-dynamic-api.track_requests', true)) {
            $userRequest = $this->userRequest;
            if (!$userRequest && $this->request->request_user_track_code) {
                $userRequest = Request::where('track_id', $this->request->request_user_track_code)->first();
            }

            // Open issue in github
            $this->openGithubIssue($userRequest);
            $model = null;
            try {
                $model = collect($this->model);
            } catch (Exception $e) {
                // Ignore. The problem it could be getting the model.
            }

            $returnObject = null;
            try {
                $returnObject = collect($this->returnObject);
            } catch (Exception $e) {
                // Ignore. The problem it could be getting the returnObject.
            }

            $failedRequestData = [
                'request_id' => $this->userRequest ?  $this->userRequest->id : null,
                'headers' => $this->request->header(),
                'accept_xml' => $this->acceptXML,
                'is_function' => $this->isFunction,
                'function' => $this->function,
                'request_output' => $this->requestOutput,
                'paginated' => $this->paginated,
                'page' => $this->page,
                'per_page' => $this->perPage,
                'sort_order' => $this->sortOrder,
                'sort_by' => $this->sortBy,
                'show_only' => $this->showOnly,
                'make_visible' => $this->makeVisible,
                'make_hidden' => $this->makeHidden,
                'with' => $this->with,
                'with_count' => $this->withCount,
                'term' => $this->term,
                'with_translations' => $this->withTranslations,
                'ips' => $this->request->ips(),
                'content_types' => $this->request->getAcceptableContentTypes(),
                'header_accept' => $this->headerAccept,
                'locale' => $this->locale,
                'model_class' => $this->modelClass,
                'model' => $model,
                'model_table' => $this->modelTable,
                'model_translation_table' => $this->modelTranslationTable,
                'relation_class' => $this->relationClass,
                'relation_output' => $this->relationOutput,
                'relation_model' => $this->relationModel,
                'specific_model' => $this->specificModel,
                'relation_specific_model' => $this->relationSpecificModel,
                'relation_bulk' => $this->relationBulk,
                'total' => $this->total,
                'output' => $this->output,
                'data' => $this->data,
                'return_object' => $returnObject,
            ];
            FailedRequest::create($failedRequestData);
        }
    }

    /** Save request and the user.
     * 
     * @since 25.03.2024
     * @author Pedro Domingos <pedro@panttera.com>
     * 
     */
    public function updateRequestFromHandler($request, $status = null, $returnOutput = null)
    {
        // Get auth user from passport
        $authUser = $request->user('api');
        if (!$authUser) {
            $authUser = Auth::user() ? Auth::user() : null;
            if (!$authUser) {
                // Get auth user from sanctum
                $authUser = auth('sanctum')->user();
            }
        }
        if (config('laravel-dynamic-api.track_requests', true)) {

            $userRequest = Request::where('track_id', $request->request_user_track_code)->first();
            if ($userRequest) {
                $userRequest->update([
                    'user_id' => $authUser ? $authUser->id : null,
                    'status' => $status,
                    'return_output' => $returnOutput,
                ]);
            }
            return $userRequest;
        }

        if ($authUser) {
            $yesterday = Carbon::now()->setHours(0)->setMinutes(0)->setSeconds(0);
            if ($authUser->last_login_at < $yesterday) {
                // UserOnline::create(['user_id' => $this->authUser->id, 'date' => Carbon::now()]);
            }
            $authUser->update(['last_login_at' => Carbon::now()]);
        }
        return null;
    }

    public function saveFailedRequestFromHandler($userRequest, $request)
    {
        if (config('laravel-dynamic-api.track_requests', true)) {
            if (!$userRequest && $this->request->request_user_track_code) {
                $userRequest = Request::where('track_id', $this->request->request_user_track_code)->first();
            }
            // Open issue in github
            $this->openGithubIssue($userRequest);

            $failedRequestData = [
                'request_id' => $userRequest ? $userRequest->id : null,
                'headers' => $request->header(),
                'content_types' => $request->getAcceptableContentTypes(),
                'ips' => $request->ips(),
            ];
            FailedRequest::create($failedRequestData);
        }
    }

    private function openGithubIssue($userRequest)
    {
        // Open issue in github
        try {
            $githubData = config('laravel-dynamic-api.github', []);
            if (
                !empty($githubData)
                && array_key_exists('token', $githubData)
                && array_key_exists('repository', $githubData)
                && array_key_exists('project', $githubData)
            ) {
                if (
                    $githubData['token'] != null &&
                    $githubData['repository'] != null &&
                    $githubData['project'] != null &&
                    $githubData['assignees'] != null &&
                    $githubData['labels'] != null
                ) {
                    $trackId = $userRequest ? $userRequest->track_id : null;
                    $status = $userRequest ? $userRequest->status : null;
                    $method = $userRequest ? $userRequest->method : null;
                    $schema = $userRequest ? $userRequest->schema : null;
                    $path = $userRequest ? $userRequest->path : null;
                    $createdAt = $userRequest ? $userRequest->created_at : null;

                    $body = "The request with the track id " . $trackId . " failed with the status code " . $status . ".\n\n"
                        . "Track ID: " . $trackId . "\n"
                        . "Date : " . $createdAt . "\n"
                        . "Status: " . $status . "\n"
                        . "Method: " . $method . "\n"
                        . "URL: " . $schema . '/' .  $path;

                    $assignees = $githubData['assignees'];
                    $labels = $githubData['labels'];

                    Http::withToken($githubData['token'])->timeout(60)->retry(3, 100)->post(
                        'https://api.github.com/repos/' . $githubData['repository'] . '/' . $githubData['project'] . '/issues',
                        [
                            "title" => "🐛 🗄️ [AUTOMATIC BUG] - An endpoint ended with the status " . $status,
                            "body" => $body,
                            "assignees" => $assignees,
                            "labels" => $labels
                        ]
                    );
                }
            }
        } catch (Exception $e) {
            // Ignore
        }
    }
}