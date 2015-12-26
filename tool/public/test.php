<?php

// Load the tool.

require_once "index.php";

// TODO: write testing code.

/*
// Initialize, all secrets are 'secret', do not set session, and do not redirect
$context = new BLTI("secret", false, false);
?>
<html>
<head>
  <title>LTI Tool Provider Testing</title>
</head>
<body>
<h1>LTI Tool Provider Testing</h1>
<p>This is an initial test to see what details are provided by the NovoEd LTI consumer.
On receiving a Basic LTI Launch, context is established if the signature checks out.
</p>
<p>in case of questions refer to sjwiles@stanford.edu</p>
<?php

if ( $context->valid ) {
    print "<pre>\n";
    print "Context Information:\n\n";
    print $context->dump();
    print "</pre>\n";
} else {
    print "<p style=\"color:red\">Could not establish context: ".$context->message."<p>\n";
}

print "<pre>\n";
print "Raw POST Parameters:\n\n";
foreach($_POST as $key => $value ) {
    print "$key=$value\n";
}
print "</pre>";
?>

<!--form action="accept-file.php" method="post" enctype="multipart/form-data">
    upload file: <input type="file" name="uploaded_file" size="25" />
    <input type="submit" name="submit" value="Submit" />
</form-->


    <script>
        window.parent.document.getElementById("basicltiLaunchFrame").height = "100px";
    </script>
</body>
</html>
*/