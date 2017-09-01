function checkCustURL()
{
    if (document.create.custom_key.value.length >= 3)
    {
        document.create.use_custom_url.checked = true;
    }
    else
    {
        document.create.use_custom_url.checked = false;
    }
    document.create.custom_key.value = document.create.custom_key.value.replace(/\W/g, '_');
}

function checkPassword()
{
    if (document.create.password.value.length >= 2)
    {
        document.create.use_password.checked = true;
    }
    else
    {
        document.create.use_password.checked = false;
    }
}

function validate()
{
    var pass = true;
    
    var error = "Please correct the following problem(s):\n";
    
    if (document.create.custom_key.value.length <= 2 && document.create.use_custom_url.checked)
    {
        pass = false;
        error += "--Your custom shortcut must be at least three characters long.";
    }
    
    if (document.create.target_url.value.length <= 4)
    {
        pass = false;
        if (error.length > 0)
        {
            error += "\n";
        }
        error += "--Your long URL is invalid.";
    }
    
    if (document.create.password.value.length <= 1 && document.create.use_password.checked)
    {
        pass = false;
        if (error.length > 0)
        {
            error += "\n";
        }
        error += "--Your password must be at least two characters long.";
    }
    
    if (!pass)
    {
        alert(error);
    }
    
    return pass;
}
