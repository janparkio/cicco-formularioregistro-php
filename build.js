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

// Function to copy individual files
function copyFile(src, dest) {
  fs.copyFileSync(src, dest);
}

// Copy directories
const directoriesToCopy = ["components", "data", "img", "lib"];
directoriesToCopy.forEach((dir) => {
  copyDir(path.join(srcDir, dir), path.join(distDir, dir));
});

// Copy individual files
const filesToCopy = [
  "index.php",
  "apple-touch-icon.png",
  "favicon-48x48.png",
  "favicon.ico",
  "favicon.svg",
  "site.webmanifest",
  "web-app-manifest-192x192.png",
  "web-app-manifest-512x512.png",
];

filesToCopy.forEach((file) => {
  copyFile(path.join(srcDir, file), path.join(distDir, file));
});

console.log("Build complete!");
