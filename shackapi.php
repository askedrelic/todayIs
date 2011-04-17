<?

class ShackAPI {
    const apiUrl = "http://www.shacknews.com/api/chat/create/17.json";

    /**
     * Post message on shacknews.
     * Handles urlencoding
     * Optional parentid parameter, if replying to an existing post
     * @return int post id of succesful post
     * @throws Exception on parse or PRL error
     */
    public static function post($username, $password, $body, $parentId=null) {
        $fields = 'content_type_id=17&content_id=17';
        $fields .= '&body='.urlencode($body);
        if (!empty($parentId)) {
            $fields .= '&parent_id='.urlencode($parentId);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);                                                
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "ShackAPI 0.1");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_URL, ShackAPI::apiUrl);

        $result = curl_exec($ch);
        return ShackApi::parseResponse($result);
    }

    /**
     * Parses ShackApi json response
     * @return int post id of succesful post
     * @throws Exception on parse or PRL error
     */
    public static function parseResponse($response) {
        $jsonResponse = json_decode($response);

        if (empty($jsonResponse)) {
            throw new Exception('Parse error');
        }

        if (isset($jsonResponse->data->error)) {
            $msg = $jsonResponse->data->error[0]->message;
            throw new Exception('PRL Error: ' . $msg);
        } else {
            return $jsonResponse->data->post_insert_id;
        }
    }
}
