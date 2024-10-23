# Step 1: Use Node.js as the base image
FROM node:18-alpine AS development

# Set the working directory
WORKDIR /app

# Copy the package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy the rest of the application code
COPY . .

# Build the application
RUN npm run build

# Expose port 3000 for the development server
EXPOSE 3000

# Start the development server
CMD ["npm", "run", "start"]
