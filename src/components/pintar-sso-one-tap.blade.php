@props([
    // 'name',
    // 'show' => false,
    // 'maxWidth' => '2xl'
])

<div>
    @unless (Auth::check())
        <iframe id="sso-one-tap" src="{{config('pintar_sso.auth_domain')}}/oauth/one-tap?client_id={{config('pintar_sso.client_id')}}&redirect_uri={{config('pintar_sso.post_login')}}" style="z-index: 999; position: fixed; right: 0px; top: 0px; width: 500px; max-width: 100vw; height: 50vh"></iframe>
        <script>
            window.addEventListener('message', function(event) {
                const isWappalyzerPlugin = !!event.data?.wappalyzer
                if (!isWappalyzerPlugin) {
                    // console.log("Message received from the child: ", event.data); // Message received from child
                }
                const isCloseCommand = event.data == "sso-one-tap-close"
                if (isCloseCommand) {
                    const element = document.getElementById("sso-one-tap");
                    element.remove();
                }

                const isConfirmCommand = event.data == "sso-one-tap-confirm"
                if (isConfirmCommand) {
                    // console.log("Confirmed: ", event.data);
                    let url = `{{config('app.url')}}/sso/login?authorized=true`
                    const {pathname} = window.location
                    if (pathname) url += `&redirect_to=${pathname}`

                    window.location.href = url
                }
            });
        </script>
    @endunless
</div>
