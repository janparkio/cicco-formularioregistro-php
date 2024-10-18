const fs = require("fs");
const path = require("path");

const srcDir = "./";
const distDir = "./dist";

// Create dist directory if it doesn't exist
if (!fs.existsSync(distDir)) {
  fs.mkdirSync(distDir);
}

// Function to copy directory recursively
function copyDir(src, dest) {
  fs.mkdirSync(dest, { recursive: true });
  let entries = fs.readdirSync(src, { withFileTypes: true });

  for (let entry of entries) {
    let srcPath = path.join(src, entry.name);
    let destPath = path.join(dest, entry.name);

    entry.isDirectory()
      ? copyDir(srcPath, destPath)
      : fs.copyFileSync(srcPath, destPath);
  }
}

// Copy necessary files and directories
copyDir("./components", path.join(distDir, "components"));
copyDir("./data", path.join(distDir, "data"));
copyDir("./lib", path.join(distDir, "lib"));

fs.copyFileSync("./index.php", path.join(distDir, "index.php"));

console.log("Build complete!");
