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

    if (entry.isDirectory()) {
      copyDir(srcPath, destPath);
    } else {
      fs.copyFileSync(srcPath, destPath);
    }
  }
}

/**
 * Creates a clean URL routing structure for PHP pages
 * Example: /register_success.php -> /register_success/index.php
 * 
 * @param {string} file - The filename to process
 * @param {string} srcPath - Source path of the file
 * @param {string} destPath - Destination path for the file
 */
function createRouterStructure(file, srcPath) {
  // Extract page name without .php extension
  const pageName = file.replace('.php', '');
  
  // Create path directly under dist/ to avoid nesting under pages/
  const pageDir = path.join(distDir, pageName);
  
  // Create directory structure for the page
  fs.mkdirSync(pageDir, { recursive: true });
  
  // Copy source PHP file as index.php in the new directory
  // This enables clean URLs like /page instead of /page.php
  fs.copyFileSync(srcPath, path.join(pageDir, 'index.php'));
}

// Function to copy individual files
function copyFile(src, dest) {
  if (!fs.existsSync(src)) {
    console.warn(`Warning: File not found: ${src}`);
    return;
  }
  fs.copyFileSync(src, dest);
}

// Copy directories
const directoriesToCopy = ["components", "data", "img", "lib"];
directoriesToCopy.forEach((dir) => {
  copyDir(path.join(srcDir, dir), path.join(distDir, dir));
});

// Files in root directory
const rootFiles = [
  "index.php",
  "apple-touch-icon.png",
  "favicon-48x48.png",
  "favicon.ico",
  "favicon.svg",
  "site.webmanifest",
  "web-app-manifest-192x192.png",
  "web-app-manifest-512x512.png",
];

// Files in pages directory
const pageFiles = [
  "registration_stats.php",
  "register_success.php"
];

// Files in lib directory
const libFiles = [
  "RegistrationLogger.php"
];

// Copy files from each directory
rootFiles.forEach(file => {
  copyFile(path.join(srcDir, file), path.join(distDir, file));
});

// Handle pages with router structure
pageFiles.forEach(file => {
  if (file.endsWith('.php') && !file.startsWith('index')) {
    createRouterStructure(file, path.join(srcDir, 'pages', file));
  } else {
    copyFile(path.join(srcDir, 'pages', file), path.join(distDir, file));
  }
});

// Copy lib files directly
libFiles.forEach(file => {
  copyFile(path.join(srcDir, 'lib', file), path.join(distDir, 'lib', file));
});

console.log("Build complete!");
