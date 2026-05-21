# Let-wish MVP

A human-centered system that transforms vague wishes into structured, achievable goals through AI-driven decomposition and planning.

## 🎯 Core Mission

Let-wish makes the wishing process less intimidating by providing structured, step-by-step guidance to turn dreams into reality. The system addresses the overwhelming questions that arise when humans start wishing or dreaming about something.

## 🏗️ Architecture Overview

### Domain-Driven Design (DDD) Approach

The system is built using Domain-Driven Design principles with clear separation of concerns:

#### Core Domain
- **Wish Structuring & Decomposition**: The system's unique value proposition - translating human wishes into achievable steps via AI

#### Supporting Domains
- **User Management**: Registration, profiles, privacy settings, history
- **Goal Tracking**: Progress visualization and monitoring

#### Generic Domains
- **Scheduling/Notifications**: Infrastructure concerns (can be off-the-shelf later)

## 🎭 Main Actors

1. **Human**: The user of the system (preferred over "User" for emotional clarity)
2. **AI Agent**: Intelligent entities that process and structure wishes

## 📊 Core Entities

### Primary Entities
- **Human**: System user who inputs wishes
- **Wish**: Raw, unstructured statement of desire (often vague or emotional)
- **Goal**: Structured, actionable objective derived from a wish
- **Step**: Smallest unit of action toward a goal

### Supporting Entities
- **Wish Draft**: Versioned input of a wish (supports edits and feedback loops)
- **Decomposer (AI Agent)**: AI entity that takes a wish and generates structured goals and steps
- **Correctness Analyzer**: AI or rule-based logic checking if the wish is actionable
- **Realization Plan**: Tree/flow of goals and steps produced from a Wish

## 🔄 Core Workflow

1. **Wish Input**: Human submits a raw wish (no validation at entry)
2. **Analysis**: Worker picks up new wishes and analyzes correctness
   - Current criteria: Wish length > 150 characters
3. **Decomposition**: AI agent breaks down valid wishes into clear, understandable goals
4. **Step Creation**: Goals are further split into easily achievable steps for any human
5. **Structured Output**: Complete realization plan with goals and actionable steps

## 🛠️ Technical Stack

- **Language**: PHP
- **Architecture**: Domain-Driven Development (DDD)
- **Testing**: Test-Driven Development (TDD)
- **Design Principles**: 
  - Composition over inheritance
  - Aggregation over inheritance
  - Heavy use of interfaces
  - Ubiquitous Language concept

## 🎯 Domain Statement

> "Human-centered wish realization through structured goal decomposition."

**Technical Definition**: A system that helps users clarify their wishes and break them down into actionable, structured, and achievable goals using automated (AI-driven) planning assistance.

## 📚 Ubiquitous Language

| Term | Description |
|------|-------------|
| Human | The user of the system. Prefer this term over "User" for emotional clarity |
| Wish | A raw, unstructured statement of desire. Often vague or emotional |
| Wish Draft | A versioned input of a wish (supporting later edits, feedback loops) |
| Goal | A structured, actionable objective derived from a wish |
| Step | A smallest unit of action toward a goal |
| Decomposer (AI Agent) | An AI entity that takes a wish and generates a structure of goals and steps |
| Correctness Analyzer | AI or rule-based logic checking if the wish is actionable or meaningful |
| Realization Plan | A tree/flow of goals and steps produced from a Wish |

## 🚀 Getting Started

### Prerequisites
- PHP 7.4 or higher
- PDO SQLite extension
- Web server (Apache, Nginx, or PHP built-in server)

### Quick Setup

1. **Run the setup script**:
   ```bash
   php setup.php
   ```

2. **Start a web server** (choose one):
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or using Apache/Nginx (point to this directory)
   ```

3. **Open your browser**:
   - Main application: http://localhost:8000/
   - View submitted wishes: http://localhost:8000/view_wishes.php

### Project Structure

```
let-wish/
├── index.html          # Main wish submission page
├── submit_wish.php     # Wish processing backend
├── view_wishes.php     # Development wish viewer
├── setup.php          # Setup and configuration script
├── wishes.db          # SQLite database (created automatically)
├── README.md          # This file
└── Context            # Original project context
```

### Features Implemented

- ✅ Beautiful, responsive wish submission interface
- ✅ Client-side validation (150+ characters required)
- ✅ Server-side validation and error handling
- ✅ SQLite database storage
- ✅ AJAX form submission with user feedback
- ✅ Development wish viewer with statistics
- ✅ Automatic database initialization
- ✅ Setup script with system checks

## 🤝 Contributing

This project follows Domain-Driven Design principles and Test-Driven Development practices. Please ensure all contributions:

- Follow DDD patterns and ubiquitous language
- Include comprehensive tests
- Use composition over inheritance
- Implement proper interfaces
- Maintain clean domain boundaries

## 📄 License

*[License information to be added]*

---

**Note**: This is an MVP version focusing on the core wish-to-goal decomposition functionality. Future iterations will include scheduling, notifications, and advanced tracking features.
