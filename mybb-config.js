const fs = require('fs');

const settingsPath = "mybb/inc/settings.php";
const domain = "localhost";
const url = `http://${domain}:8000`;

// Check for settings file existance
const stats = fs.statSync(settingsPath);
writeSettings();
fs.appendFile(
    settingsPath,
    `\n\$settings['bburl'] = \"${url}\";\n\$settings['cookiedomain'] = \".${domain}\";\n\$settings['cookiepath'] = \"\";\n`,
    (err) => {if (null !== err) {throw new Error(err);}}
);
