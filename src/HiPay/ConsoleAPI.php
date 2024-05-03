<?php
/**
 * HiPay Console API client library for PHP
 *
 * @package     Library
 * @author      Stéphane Aulery
 * @version     0.1.0
 * @copyright   Copyright (c) 2024, LkpPo
 * @license https://www.isc.org/licenses/ ISC License
 * @see https://github.com/lkppo/hipay-console-api
 */

/**
 * HiPay client library for PHP
 *
 * @see https://support.hipay.com/hc/en-us/articles/360020476679-Automatic-export-via-the-Console-API
 * @see https://console.hipay.com/api/docs
 * @see https://stage-console.hipay.com/api/docs
 *
 * @package LkpPo\HiPay
 */
namespace LkpPo\HiPay
{
    const PROD_CONSOLE_API_URL = "https://console.hipay.com/api";
    const STAGE_CONSOLE_API_URL = "https://stage-console.hipay.com/api";
    
	/**
	 * HiPay Console API client class
	 * Client class for HiPay Console API
	 */
	class ConsoleAPI
	{
		/**
		 * API auth token
         *
         * Token example:
         * [
         *    "token_type"   => "Bearer", // Type of token
         *    "expires_in"   => 3600,     // Expiration delay in second ?
         *    "access_token" => "eyJ...", // A very long JWT token string
         * ]
		 */
		private $auth_token = [];
		
		/**
		 * API base URL
		 */
		private string $api_url = "";
        
		
		/**
		 * Object constructor
		 *
		 * Memorize the API URL
		 *
		 * @param string $api_url
		 */
		public function __construct(
            string $api_url,
        )
        {
            $this->api_url = rtrim($api_url, "/") . "/";
		}
		
		/**
		 * Object destructor
		 */
		public function __destruct()
		{
			
		}
		
		
		///
		/// Other Functions
		////////////////////////////////////////////////////////////////
		
		/**
		 * Generates token to authenticate user
		 *
		 * The token is returned and also stored internally for subsequent calls where it is automatically included.
         *
         * N.B.: The token is not automatically renewed upon expiration.
		 *
		 * Implements GET /api/login
		 *
		 * @param string $username Login of an HiPay Console user
		 * @param string $password Password of the same HiPay Console user
         
		 * @return array
		 */
		public function login(
            string $username,
            string $password,
        ): array
        {
			$aResponse = $this->__curl_post("login", ["username" => $username, "password" => $password]);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            $this->auth_token = $response ?? [];
            
            return $aResponse;
		}
		
		
		///
		/// Managing export files
		////////////////////////////////////////////////////////////////
		
		/**
		 * Download the export file
		 *
		 * Implements GET /api/export-files/{id}
		 *
		 * @param int $id Export file ID
		 * @param string $hash Hash of requested file [returned by listExportFile()]
		 * @param string $destination_path Path where the export file will be downloaded
		 *
		 * @return array
		 */
		public function downloadExportFile(
            int $id,
            string $hash,
            string $destination_path,
        ): array
        {
            $aResponse = $this->__curl_download("export-files/" . $id . "?" . $this->build_query(["hash" => $hash]), $destination_path);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Send by email the export file
		 *
		 * Implements GET /api/export-files/{id}/email
		 *
		 * @param int $id Export file ID
		 * @param string $hash Hash of requested file [returned by listExportFile()]
		 *
		 * @return array
		 */
		public function sendExportFile(
            int $id,
            string $hash,
        ): array
        {
			$aResponse = $this->__curl_get("export-files/" . $id . "/email?" . $this->build_query(["hash" => $hash]));
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Generate file again in the same conditions it has been generated the first times
		 *
		 * Implements GET /api/export-files/{id}/regenerate
		 *
		 * @param int $id Export file ID
		 * @param string $hash Hash of requested file [returned by listExportFile()]
		 * @param bool $send_by_email Should send (or not) export file by email (Default is FALSE)
		 *
		 * @return array
		 */
		public function regenerateExportFile(
            int $id,
            string $hash,
            bool $send_by_email = FALSE,
        ): array
        {
            $aParam = [
                "hash" => $hash,
                "send_by_email" => ($send_by_email ? "true" : "false"),
            ];
            
			$aResponse = $this->__curl_get("export-files/" . $id . "/regenerate?" . $this->build_query($aParam));
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Retrieves the collection of ExportFile resources
		 *
		 * Implements GET /api/exports/{exportId}/files
		 *
		 * @param int $exportId Export ID
		 * @param string $dateCreated File creation date (format: YYYY-MM-DD)
		 * @param string $status Export status (possible values: created, stocked, sent, expired, send_error, generate_error)
		 * @param string $filename Name of generated file
		 * @param string $dateRegenerated File regeneration date (format: YYYY-MM-DD)
		 *
		 * @return array
		 */
		public function listExportFile(
            int $exportId,
            string $dateCreated = "",
            string $status = "",
            string $filename = "",
            string $dateRegenerated = "",
        ): array
        {
            $aParam = [
                "dateCreated" => $dateCreated,
                "status" => $status,
                "filename" => $filename,
                "dateRegenerated" => $dateRegenerated,
            ];
            
			$aResponse = $this->__curl_get("exports/" . $exportId . "/files?" . $this->build_query($aParam));
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Creates a ExportFile resource
		 *
		 * Implements POST /api/exports/{exportId}/files
         *
         * Format of $data parameter:
         * {
         *   "export": "string",
         *   "status": "string",
         *   "nbItems": "string",
         *   "dateRegenerated": "2024-04-25T14:15:27.403Z"
         * }
		 *
		 * @param int $exportId Export ID
		 * @param array $data Configuration of the new export File resource (will be converted to JSON)
		 *
		 * @return array
		 */
		public function createExportFile(
            int $exportId,
            array $data,
        ): array
        {
			$aResponse = $this->__curl_post("exports/" . $exportId . "/files", $data);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		
		///
		/// Managing exports
		////////////////////////////////////////////////////////////////
		
		/**
		 * Retrieves the collection of Export resources
		 *
		 * Implements GET /api/exports
         *
         * Format of $filter parameter:
         * {
         *      "module": "string",
         *      "dateCreated": "string",
         *      "status": "string",
         *      "filePrefix": "string",
         *      "recurrence": "string",
         *      "receiveByEmail": "string",
         *      "config": "string",
         *      "withExportFiles": "string"
         * }
		 *
		 * @param array $filter Query options ; only module option is mandatory (will be converted to JSON)
		 *
		 * @return array
		 */
		public function listExport(
            array $filter,
        ): array
        {
			$aResponse = $this->__curl_get("exports", $filter);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Creates a new export
		 *
		 * Implements POST /api/exports
         *
         * Format of $data parameter:
         * {
         *     "dateUpdated": "2024-04-25T12:58:20.499Z",
         *     "filePrefix": "ALL_ACCOUNTS",
         *     "module": "transaction",
         *     "status": "active",
         *     "columns": "{\"merchant_order_id\":\"Order ID\"}",
         *     "filters": "{\"payment_means\":\"visa--mastercard\",\"order_by\":\"created_date\",\"direction\":\"desc\",\"created_date_interval\":\"pd\",\"authorized_by_payment_means_date_interval\":\"custom\",\"authorized_by_payment_means_date_from\":\"2019-01-01\",\"authorized_by_payment_means_date_to\":\"2019-06-15\"}",
         *     "separator": "semicolon",
         *     "recurrence": "once",
         *     "recurrenceDay": "1",
         *     "nbOccurrence": 0,
         *     "endRecurrenceDay": "Unknown Type: datetime",
         *     "receiveByEmail": true,
         *     "user": "string",
         *     "totalHits": 0,
         *     "config": "{granularity: operation} | {granularity: transaction}",
         *     "withExportFiles": "string",
         *     "error": "Unknown Type: json"
         * }
		 *
		 * @param array $data Export configuration
		 *
		 * @return array
		 */
		public function createExport(
            array $data,
        ): array
        {
			$aResponse = $this->__curl_post("exports", $data);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Retrieves the collection of Export resources
		 *
		 * Implements GET /api/exports/trending-balance
		 *
		 * @return array
		 */
		public function listExportTrendingBalance(
        ): array
        {
			$aResponse = $this->__curl_get("exports/trending-balance");
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Retrieves a Export resource
		 *
		 * Implements POST /api/exports/{id}
		 *
		 * @param int $id Export ID
		 * @param bool $withExportFiles Includes or not list of export files in response (Default is TRUE)
		 *
		 * @return array
		 */
		public function getExport(
            int $id,
            bool $withExportFiles = TRUE
        ): array
        {
			$aResponse = $this->__curl_get("exports/" . $id . "?withExportFiles=" . ($withExportFiles ? "true" : "false"));
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Removes the Export resource
		 *
		 * Implements DELETE /api/exports/{id}
		 *
		 * @param int $id Export ID
		 *
		 * @return array
		 */
		public function deleteExport(
            int $id,
        ): array
        {
			$aResponse = $this->__curl_delete("exports/" . $id);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		/**
		 * Replaces the Export resource
		 *
		 * Implements PUT /api/exports/{id}
         *
         * Format of $data parameter:
         * {
         *     "dateUpdated": "2024-04-25T12:58:20.499Z",
         *     "filePrefix": "ALL_ACCOUNTS",
         *     "module": "transaction",
         *     "status": "active",
         *     "columns": "{\"merchant_order_id\":\"Order ID\"}",
         *     "filters": "{\"payment_means\":\"visa--mastercard\",\"order_by\":\"created_date\",\"direction\":\"desc\",\"created_date_interval\":\"pd\",\"authorized_by_payment_means_date_interval\":\"custom\",\"authorized_by_payment_means_date_from\":\"2019-01-01\",\"authorized_by_payment_means_date_to\":\"2019-06-15\"}",
         *     "separator": "semicolon",
         *     "recurrence": "once",
         *     "recurrenceDay": "1",
         *     "nbOccurrence": 0,
         *     "endRecurrenceDay": "Unknown Type: datetime",
         *     "receiveByEmail": true,
         *     "user": "string",
         *     "totalHits": 0,
         *     "config": "{granularity: operation} | {granularity: transaction}",
         *     "withExportFiles": "string",
         *     "error": "Unknown Type: json"
         * }
		 *
		 * @param int $id Export ID
		 * @param array $data Export configuration (will be converted to JSON)
		 *
		 * @return array
		 */
		public function replaceExport(
            string $id,
            array $data,
        ): array
        {
			$aResponse = $this->__curl_put("exports/" . $id, $data);
			
            $response = @json_decode($aResponse["response"], TRUE);
            if (json_last_error() != JSON_ERROR_NONE)
                $aResponse["response"] = [];
			else
                $aResponse["response"] = $response;
            
            return $aResponse;
		}
		
		
		///
		/// Private Utils Functions
		////////////////////////////////////////////////////////////////
		
		/**
		 * Send a DELETE HTTP request
		 *
		 * @param string $relative_url Url (without the base api part)
		 * @param array $hearders Array of additional headers
		 *
		 * @return array
		 */
		private function __curl_delete(
            string $relative_url,
            array $hearders = NULL,
        ): array
		{
	        $ch = curl_init();
	        
	        curl_setopt($ch, CURLOPT_URL, $this->api_url . $relative_url);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
	        
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->__merge_headers($hearders));
	        
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	        
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	        
	        // Disables certificate verification and proxy usage
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	        curl_setopt($ch, CURLOPT_PROXY, NULL);
	        
	        // Connection timeout in seconds
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	        
	        // Execution timeout in seconds
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	        
	        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	        
	        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	        
	        $response = curl_exec($ch);
	        if ($response === FALSE)
	        {
	            $response = "";
	        }
	        
	        $errno_http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	        $errmsg_http = $this->__http_strerror($errno_http);
	        
	        $errno_curl = curl_errno($ch);
	        $errmsg_curl = curl_strerror($errno_curl);
	
		    curl_close($ch);
	        
	        return [
	            "errno_curl" => $errno_curl,
	            "errmsg_curl" => $errmsg_curl,
	            "errno_http" => $errno_http,
	            "errmsg_http" => $errmsg_http,
	            "response" => $response,
	        ];
		}
		
		/**
		 * Send a GET HTTP request
		 *
		 * @param string $relative_url Url (without the base api part)
		 * @param array $hearders Array of additional headers
		 *
		 * @return array
		 */
		private function __curl_get(
            string $relative_url,
            array $hearders = NULL,
        ): array
		{
	        $ch = curl_init();
	        
	        curl_setopt($ch, CURLOPT_URL, $this->api_url . $relative_url);
	        
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->__merge_headers($hearders));
	        
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	        
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	        
	        // Disables certificate verification and proxy usage
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	        curl_setopt($ch, CURLOPT_PROXY, NULL);
	        
	        // Connection timeout in seconds
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	        
	        // Execution timeout in seconds
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	        
	        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	        
	        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	        
	        $response = curl_exec($ch);
	        if ($response === FALSE)
	        {
	            $response = "";
	        }
	        
	        $errno_http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	        $errmsg_http = $this->__http_strerror($errno_http);
	        
	        $errno_curl = curl_errno($ch);
	        $errmsg_curl = curl_strerror($errno_curl);
	
		    curl_close($ch);
	        
	        return [
	            "errno_curl" => $errno_curl,
	            "errmsg_curl" => $errmsg_curl,
	            "errno_http" => $errno_http,
	            "errmsg_http" => $errmsg_http,
	            "response" => $response,
	        ];
		}
		
		/**
		 * Send a POST HTTP request
		 *
		 * @param string $relative_url Url (without the base api part)
		 * @param array $data Body data (serialized to JSON)
		 * @param array $hearders Array of additional headers
		 *
		 * @return array
		 */
		private function __curl_post(
            string $relative_url,
            array $data = NULL,
            array $hearders = NULL,
        ): array
		{
	        $ch = curl_init();
	        
	        curl_setopt($ch, CURLOPT_URL, $this->api_url . $relative_url);
	        curl_setopt($ch, CURLOPT_POST, TRUE);
	        
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->__merge_headers($hearders));
	        
	        if (!is_null($data))
	        {
	            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	        }
	        
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	        
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	        
	        // Disables certificate verification and proxy usage
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	        curl_setopt($ch, CURLOPT_PROXY, NULL);
	        
	        // Connection timeout in seconds
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	        
	        // Execution timeout in seconds
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	        
	        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	        
	        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	        
	        $response = curl_exec($ch);
	        if ($response === FALSE)
	        {
	            $response = "";
	        }
	        
	        $errno_http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	        $errmsg_http = $this->__http_strerror($errno_http);
	        
	        $errno_curl = curl_errno($ch);
	        $errmsg_curl = curl_strerror($errno_curl);
	
		    curl_close($ch);
	        
	        return [
	            "errno_curl" => $errno_curl,
	            "errmsg_curl" => $errmsg_curl,
	            "errno_http" => $errno_http,
	            "errmsg_http" => $errmsg_http,
	            "response" => $response,
	        ];
		}
		
		/**
		 * Send a PUT HTTP request
		 *
		 * @param string $relative_url Url (without the base api part)
		 * @param array $data Body data (serialized to JSON)
		 * @param array $hearders Array of additional headers
		 *
		 * @return array
		 */
		private function __curl_put(
            string $relative_url,
            array $data = NULL,
            array $hearders = NULL,
        ): array
		{
	        $ch = curl_init();
	        
	        curl_setopt($ch, CURLOPT_URL, $this->api_url . $relative_url);
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	        
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->__merge_headers($hearders));
	        
	        if (!is_null($data))
	        {
	            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	        }
	        
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
	        
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	        curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	        
	        // Disables certificate verification and proxy usage
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	        curl_setopt($ch, CURLOPT_PROXY, NULL);
	        
	        // Connection timeout in seconds
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	        
	        // Execution timeout in seconds
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	        
	        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	        
	        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
	        
	        $response = curl_exec($ch);
	        if ($response === FALSE)
	        {
	            $response = "";
	        }
	        
	        $errno_http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	        $errmsg_http = $this->__http_strerror($errno_http);
	        
	        $errno_curl = curl_errno($ch);
	        $errmsg_curl = curl_strerror($errno_curl);
	
		    curl_close($ch);
	        
	        return [
	            "errno_curl" => $errno_curl,
	            "errmsg_curl" => $errmsg_curl,
	            "errno_http" => $errno_http,
	            "errmsg_http" => $errmsg_http,
	            "response" => $response,
	        ];
		}
    
    
        /**
         * Télécharger un fichier depuis une URL avec le protocole HTTP(S)
         *
         * @param string $url URL de la ressource web distante
         * @param string $file Chemin du fichier cible local
         *
         * @return bool TRUE si le téléchargement est réussi, FALSE autrement
         */
		
		/**
		 * Download a file from an url with HTTP
		 *
		 * @param string $source_url Url (without the base api part)
		 * @param string $destination_path Path where the file will be written
		 *
		 * @return array
		 */
        private function __curl_download(
            string $source_url,
            string $destination_path
        ): array
        {
            $fp = fopen($destination_path, 'wb');
            $ch = curl_init();
            
	        curl_setopt($ch, CURLOPT_URL, $this->api_url . $source_url);
	        
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->__merge_headers([]));
            
            //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE);
	        
	        // Disables certificate verification and proxy usage
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	        curl_setopt($ch, CURLOPT_PROXY, NULL);
	        
	        // Connection timeout in seconds
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
	        
	        // Execution timeout in seconds
	        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            //curl_setopt($ch, CURLOPT_MAXREDIRS, 32);
	        
	        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
	        
	        curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
            
            curl_setopt($ch, CURLOPT_FILE, $fp);
            
	        $response = curl_exec($ch);
	        if ($response === FALSE)
	        {
	            $response = "";
	        }
	        
	        $errno_http = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
	        $errmsg_http = $this->__http_strerror($errno_http);
	        
	        $errno_curl = curl_errno($ch);
	        $errmsg_curl = curl_strerror($errno_curl);
	
		    curl_close($ch);
            
            fclose($fp);
	        
	        return [
	            "errno_curl" => $errno_curl,
	            "errmsg_curl" => $errmsg_curl,
	            "errno_http" => $errno_http,
	            "errmsg_http" => $errmsg_http,
	            "response" => $response,
	        ];
        }
		
	    /**
	     * Return the english name associated with an HTTP error code
	     *
	     * @param int|string $errno HTTP error code
	     *
	     * @return string
	     */
		private function __http_strerror(
            int|string $errno
        ): string
		{
	        if (is_string($errno) && is_numeric)
	        {
	            if (is_numeric($errno))
	            {
	                $errno = intval($errno);
	            }
	            else
	            {
	                $errno = 0;
	            }
	        }
	        
            $http_status_codes = [
                // 1xx informational response
                100 => "Continue",
                101 => "Switching Protocols",
                102 => "Processing",
                103 => "Early Hints",
                
                // 2xx success
                200 => "OK",
                201 => "Created",
                202 => "Accepted",
                203 => "Non-Authoritative Information",
                204 => "No Content",
                205 => "Reset Content",
                206 => "Partial Content",
                207 => "Multi-Status",
                208 => "Already Reported",
                226 => "IM Used",
                
                // 3xx redirection
                300 => "Multiple Choices",
                301 => "Moved Permanently",
                302 => "Found",
                303 => "See Other",
                304 => "Not Modified",
                305 => "Use Proxy",
                306 => "Switch Proxy",
                307 => "Temporary Redirect",
                308 => "Permanent Redirect",
                
                // 4xx client errors
                400 => "Bad Request",
                401 => "Unauthorized",
                402 => "Payment Required",
                403 => "Forbidden",
                404 => "Not Found",
                405 => "Method Not Allowed",
                406 => "Not Acceptable",
                407 => "Proxy Authentication Required",
                408 => "Request Timeout",
                409 => "Conflict",
                410 => "Gone",
                411 => "Length Required",
                412 => "Precondition Failed",
                413 => "Payload Too Large",
                414 => "URI Too Long",
                415 => "Unsupported Media Type",
                416 => "Range Not Satisfiable",
                417 => "Expectation Failed",
                418 => "I'm a teapot",
                421 => "Misdirected Request",
                422 => "Unprocessable Entity",
                423 => "Locked",
                424 => "Failed Dependency",
                425 => "Too Early",
                426 => "Upgrade Required",
                428 => "Precondition Required",
                429 => "Too Many Requests",
                431 => "Request Header Fields Too Large",
                451 => "Unavailable For Legal Reasons",
                
                // 5xx server errors
                500 => "Internal Server Error",
                501 => "Not Implemented",
                502 => "Bad Gateway",
                503 => "Service Unavailable",
                504 => "Gateway Timeout",
                505 => "HTTP Version Not Supported",
                506 => "Variant Also Negotiates",
                507 => "Insufficient Storage",
                508 => "Loop Detected",
                510 => "Not Extended",
                511 => "Network Authentication Required",
                
                // DeepL specific code
                456 => "Quota exceeded",
                529 => "Too many requests",
            ];
            
            return $http_status_codes[$errno] ?? "";
		}
		
	    /**
	     * Merge custom headers with internal headers
         *
         * Internal headers allow to silently include the auth token, the Content-Type and Accept headers.
         *
         * Custom headers can overwrite internal headers.
         *
         * N.B.: if the login method have not been called first, the auth token is not included.
	     *
	     * @param array $custom_headers Custom headers array
	     *
	     * @return array
	     */
        private function __merge_headers(
            ?array $custom_headers
        ): array
        {            
            $internal_hearders = [
                "Content-Type:application/json",
                "Accept:application/json",
            ];
            
            if (is_array($this->auth_token) && array_key_exists("token_type", $this->auth_token) && array_key_exists("access_token", $this->auth_token))
            {
                $internal_hearders[] = "x-Authorization: " . $this->auth_token["token_type"] . " " . $this->auth_token["access_token"];
            }
            
            return array_merge($internal_hearders, $custom_headers ?? []) ?? [];
        }
        
        
        /**
         * Generate URL-encoded query string according to RFC 3986
         *
         * This function is a wrapper for http_build_query() where $encoding_type parameter is always PHP_QUERY_RFC3986 and the separator '&'.
         *
         * @param mixed $data Array or object
         *
         * @return string
         */
        private function build_query(
            array|object $data,
        ): string
        {
            return http_build_query($data, "", "&", PHP_QUERY_RFC3986);
        }
	}
}
