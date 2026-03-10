#!/bin/bash

# Replace 'artists.txt' with your actual text file path if it's different
input_file="artists.txt"

# Check if the file exists
if [ ! -f "$input_file" ]; then
    echo "Error: File '$input_file' not found. Please check the path."
    exit 1
fi

# Read the file line by line and create directories
while IFS= read -r artist; do
    # Skip empty lines
    if [ -n "$artist" ]; then
        mkdir -p "$artist"
        echo "Created directory: $artist"
    fi
done < "$input_file"

echo "Done! All directories created."
