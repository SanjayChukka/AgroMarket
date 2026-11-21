// The activation notice has been displayed. Remove its flag from the address bar.
const loginUrl = new URL(window.location.href);
if (loginUrl.searchParams.has("activated") || loginUrl.searchParams.has("activation")) {
    loginUrl.searchParams.delete("activated");
    loginUrl.searchParams.delete("activation");
    window.history.replaceState(window.history.state, "", loginUrl);
}
