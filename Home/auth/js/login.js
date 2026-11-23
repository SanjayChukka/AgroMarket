
/* Remove Activation Parameters from URL */
const loginUrl = new URL(window.location.href);

if (
    loginUrl.searchParams.has("activated") ||
    loginUrl.searchParams.has("activation")
) {
    loginUrl.searchParams.delete("activated");
    loginUrl.searchParams.delete("activation");

    window.history.replaceState(
        window.history.state,
        "",
        loginUrl
    );
}
