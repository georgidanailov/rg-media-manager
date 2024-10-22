# Step 1: Use Node.js as the base image
FROM node:18-alpine AS build

# Set the working directory
WORKDIR /app

# Copy the package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application code
COPY . .

# Build the React app for production
RUN npm run build

# Step 2: Use Nginx to serve the build files
FROM nginx:alpine

# Copy the build files to the Nginx html folder
COPY --from=build /app/build /usr/share/nginx/html

# Expose port 80 to allow external connections
EXPOSE 80

# Start Nginx when the container starts
CMD ["nginx", "-g", "daemon off;"]
