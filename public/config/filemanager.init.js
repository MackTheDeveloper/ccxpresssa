$('.fm-container').richFilemanager({
   baseUrl: '../public',
    callbacks: {
        beforeCreateImageUrl: function (resourceObject, url) {
            
            return url += 'modified=ImageUrl';
        },
        beforeCreatePreviewUrl: function (resourceObject, url) {
            
            return url += '&modified=previewUrl';
        },
        beforeSelectItem: function (resourceObject, url) {
        	
            return url += '&modified=selectItem';
        },
        afterSelectItem: function (resourceObject, url) {
            // example on how to set url into target input and close bootstrap modal window
            // assumes that filemanager is opened via iframe, which is at the same domain as parent
            // https://developer.mozilla.org/en-US/docs/Web/Security/Same-origin_policy
            $('#target-input', parent.document).val(url);
            $('#modal', parent.document).find('.close').click();
        },
        beforeSetRequestParams: function (requestMethod, requestParams) {

            // add "jwt" parameter with "your_token" value to both GET and POST requests
            if (requestMethod === 'POST' && $.isArray(requestParams)) { // form parameters
                requestParams.push({name: "jwt", value: "your_token"});
            } else {
                requestParams.jwt = 'your_token';
            }
            return requestParams;
        },
        beforeSendRequest: function (requestMethod, requestParams) {
            // prevent all GET requests that lack "jwt" request parameter
            if (requestMethod === 'GET' && requestParams.jwt === 'undefined') {
                return false;
            }
            return true;
        }
    }
});