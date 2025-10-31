// Futuristic Theme Functions
function initializeFuturisticTheme() {
  // Add smooth scrolling
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Add intersection observer for animations
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1"
        entry.target.style.transform = "translateY(0)"
      }
    })
  }, observerOptions)

  // Observe elements for animation
  document.querySelectorAll(".step-card, .feature-card").forEach((el) => {
    el.style.opacity = "0"
    el.style.transform = "translateY(30px)"
    el.style.transition = "all 0.6s ease"
    observer.observe(el)
  })
}

// Tab Switching with Modern Effects
function switchTab(tabName) {
  // Remove active class from all tabs and content
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.classList.remove("active")
  })
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.classList.remove("active")
  })

  // Add active class to clicked tab and corresponding content
  event.target.classList.add("active")
  document.getElementById(tabName + "-tab").classList.add("active")

  // Add ripple effect
  createRippleEffect(event.target)
}

// Create ripple effect for buttons
function createRippleEffect(element) {
  const ripple = document.createElement("div")
  ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
    `

  const rect = element.getBoundingClientRect()
  const size = Math.max(rect.width, rect.height)
  ripple.style.width = ripple.style.height = size + "px"
  ripple.style.left = rect.width / 2 - size / 2 + "px"
  ripple.style.top = rect.height / 2 - size / 2 + "px"

  element.style.position = "relative"
  element.appendChild(ripple)

  setTimeout(() => {
    if (ripple.parentNode) {
      ripple.parentNode.removeChild(ripple)
    }
  }, 600)
}

// Enhanced Voice Input
function startVoiceInput() {
  if (!("webkitSpeechRecognition" in window) && !("SpeechRecognition" in window)) {
    showNotification("Speech recognition is not supported in your browser.", "error")
    return
  }

  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
  const recognition = new SpeechRecognition()

  recognition.continuous = false
  recognition.interimResults = false
  recognition.lang = "en-US"

  const voiceBtn = document.querySelector(".voice-btn")
  const textarea = document.getElementById("text-content")

  recognition.onstart = () => {
    voiceBtn.innerHTML = '<i class="fas fa-stop"></i>'
    voiceBtn.style.background = "linear-gradient(135deg, #ef4444, #dc2626)"
    voiceBtn.title = "Stop recording"

    // Add pulsing animation
    voiceBtn.style.animation = "pulse 1s infinite"
  }

  recognition.onresult = (event) => {
    const transcript = event.results[0][0].transcript
    textarea.value = transcript
    textarea.focus()
    showNotification("Voice input captured successfully!", "success")
  }

  recognition.onend = () => {
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>'
    voiceBtn.style.background = "var(--gradient-primary)"
    voiceBtn.title = "Voice input"
    voiceBtn.style.animation = ""
  }

  recognition.onerror = (event) => {
    console.error("Speech recognition error:", event.error)
    voiceBtn.innerHTML = '<i class="fas fa-microphone"></i>'
    voiceBtn.style.background = "var(--gradient-primary)"
    voiceBtn.title = "Voice input"
    voiceBtn.style.animation = ""

    let errorMessage = "Voice recognition failed. "
    switch (event.error) {
      case "no-speech":
        errorMessage += "No speech detected."
        break
      case "audio-capture":
        errorMessage += "No microphone found."
        break
      case "not-allowed":
        errorMessage += "Microphone access denied."
        break
      default:
        errorMessage += "Please try again."
    }
    showNotification(errorMessage, "error")
  }

  recognition.start()
}

// Enhanced Text-to-Speech with AI Voice
function speakResult() {
  if (!("speechSynthesis" in window)) {
    showNotification("Text-to-speech is not supported in your browser.", "error");
    return;
  }

  const speakBtn = document.querySelector(".speak-btn");
  const originalContent = speakBtn.innerHTML;

  // If already speaking, stop and reset
  if (speechSynthesis.speaking || speechSynthesis.pending) {
    speechSynthesis.cancel();
    resetSpeakButton(speakBtn, originalContent);
    showNotification("🔇 Narration stopped", "info");
    return;
  }

  // Gather analysis data
  const confidence = document.querySelector(".percentage")?.textContent || "Unknown";
  const label = document.querySelector(".label")?.textContent || "Unknown";
  const explanation = document.querySelector(".result-explanation p")?.textContent || "";
  const analysisType = document.querySelector(".tab-btn.active")?.textContent?.trim() || "content";

  const speechText = createSpeechText(confidence, label, explanation, analysisType);

  if (!speechText) {
    showNotification("No analysis result to read.", "error");
    return;
  }

  const utterance = new SpeechSynthesisUtterance(speechText);

  // Set up utterance event listeners
  utterance.onstart = () => {
    console.log("AI narration started");
    speakBtn.innerHTML = '<i class="fas fa-stop"></i><span></span>';
    speakBtn.classList.add("speaking");
    speakBtn.title = "";
    showNotification("🎤 AI is reading the analysis...", "info");
  };

  utterance.onend = () => {
    console.log("AI narration completed");
    resetSpeakButton(speakBtn, originalContent);
    showNotification("✅ AI narration completed", "success");
  };

  utterance.onerror = (event) => {
    console.error("Speech error:", event.error);
    resetSpeakButton(speakBtn, originalContent);
    showNotification("❌ AI narration failed: " + event.error, "error");
  };

  // Ensure voices are loaded before continuing
  loadVoices().then(() => {
    configureSpeechVoice(utterance);
    speechSynthesis.speak(utterance);
  });
}

// Load voices asynchronously (handles delay)
function loadVoices() {
  return new Promise((resolve) => {
    let voices = speechSynthesis.getVoices();
    if (voices.length) return resolve(voices);

    speechSynthesis.onvoiceschanged = () => {
      voices = speechSynthesis.getVoices();
      resolve(voices);
    };
  });
}

// Create comprehensive speech text
function createSpeechText(confidence, label, explanation, analysisType) {
  const cleanConfidence = confidence.replace("%", " percent");
  const cleanLabel = label.toLowerCase();

  let speechText = `VeriFact AI Analysis Report. `;
  speechText += `I have completed the analysis of your ${analysisType.toLowerCase()}. `;
  speechText += `The content is ${cleanConfidence} ${cleanLabel}. `;

  if (explanation && explanation.length > 10) {
    const cleanExplanation = explanation
      .replace(/\n+/g, ". ")
      .replace(/\s+/g, " ")
      .replace(/([.!?])\s*([A-Z])/g, "$1 $2")
      .replace(/https?:\/\/[^\s]+/g, "source link")
      .trim();

    speechText += `Here is the detailed analysis: ${cleanExplanation} `;
  }

  const tips = document.querySelectorAll(".tip-content p");
  if (tips.length > 0) {
    speechText += `Here are some cybersecurity recommendations: `;
    tips.forEach((tip, index) => {
      if (index < 3) {
        speechText += `${index + 1}. ${tip.textContent.trim()}. `;
      }
    });
  }

  speechText += `This concludes the VeriFact AI analysis. Stay vigilant against misinformation.`;

  return speechText;
}

// Configure speech voice for AI-like experience
function configureSpeechVoice(utterance) {
  const voices = speechSynthesis.getVoices();

  const preferredVoices = [
    "Google UK English Female",
    "Google US English",
    "Microsoft Zira Desktop",
    "Microsoft David Desktop",
    "Alex", "Samantha", "Karen",
  ];

  let selectedVoice = null;
  for (const voiceName of preferredVoices) {
    selectedVoice = voices.find((voice) => voice.name.includes(voiceName));
    if (selectedVoice) break;
  }

  if (!selectedVoice) {
    selectedVoice = voices.find((voice) => voice.lang.startsWith("en"));
  }

  if (selectedVoice) {
    utterance.voice = selectedVoice;
  }

  utterance.rate = 0.85;
  utterance.pitch = 1.0;
  utterance.volume = 0.9;
}

// Reset speak button to original state
function resetSpeakButton(button, originalContent) {
  button.innerHTML = originalContent;
  button.classList.remove("speaking");
  button.title = "Listen to AI Analysis";
}

// Enhanced copy result function
function copyResult() {
  const confidence = document.querySelector(".percentage")?.textContent || ""
  const label = document.querySelector(".label")?.textContent || ""
  const explanation = document.querySelector(".result-explanation p")?.textContent || ""
  const timestamp = new Date().toLocaleString()

  const textToCopy = `VeriFact AI Analysis Report
Generated: ${timestamp}

Result: ${confidence} ${label}

Analysis:
${explanation}

Verified by VeriFact AI - Fighting misinformation with technology.
Visit: https://verifact.ai`

  navigator.clipboard
    .writeText(textToCopy)
    .then(() => {
      const copyBtn = document.querySelector(".copy-btn")
      const originalContent = copyBtn.innerHTML
      copyBtn.innerHTML = '<i class="fas fa-check"></i><span>Copied!</span>'
      copyBtn.classList.add("success")

      setTimeout(() => {
        copyBtn.innerHTML = originalContent
        copyBtn.classList.remove("success")
      }, 2000)

      showNotification("✅ Analysis copied to clipboard", "success")
    })
    .catch(() => {
      showNotification("❌ Failed to copy to clipboard", "error")
    })
}

// Share result function
function shareResult() {
  const confidence = document.querySelector(".percentage")?.textContent || ""
  const label = document.querySelector(".label")?.textContent || ""
  const shareText = `VeriFact AI Analysis: ${confidence} ${label} - Verified by AI-powered fact-checking technology.`

  if (navigator.share) {
    navigator
      .share({
        title: "VeriFact Analysis Result",
        text: shareText,
        url: window.location.href,
      })
      .then(() => {
        showNotification("✅ Analysis shared successfully", "success")
      })
      .catch(() => {
        // Fallback to clipboard
        fallbackShare(shareText)
      })
  } else {
    // Fallback for browsers without Web Share API
    fallbackShare(shareText)
  }
}

// Fallback share function
function fallbackShare(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showNotification("📋 Share text copied to clipboard", "success")
    })
    .catch(() => {
      showNotification("❌ Sharing failed", "error")
    })
}

// Initialize speech synthesis voices (needed for some browsers)
function initializeSpeechSynthesis() {
  if ("speechSynthesis" in window) {
    // Load voices
    speechSynthesis.getVoices()

    // Some browsers need this event
    speechSynthesis.onvoiceschanged = () => {
      console.log("Speech synthesis voices loaded:", speechSynthesis.getVoices().length)
    }
  }
}

// Auto-speak result option (can be enabled/disabled)
function autoSpeakResult() {
  const autoSpeakEnabled = localStorage.getItem("verifact-auto-speak") === "true"
  if (autoSpeakEnabled) {
    setTimeout(() => {
      speakResult()
    }, 1000) // Delay to ensure UI is ready
  }
}

// Toggle auto-speak setting
function toggleAutoSpeak() {
  const currentSetting = localStorage.getItem("verifact-auto-speak") === "true"
  const newSetting = !currentSetting
  localStorage.setItem("verifact-auto-speak", newSetting)

  showNotification(newSetting ? "🔊 Auto-speak enabled" : "🔇 Auto-speak disabled", "info")
}

// File Upload Enhancement
document.addEventListener("DOMContentLoaded", () => {
  initializeFuturisticTheme()
  setupFileUploads()
  setupFormValidation()
  initializeSpeechSynthesis() // Add this line
})

function setupFileUploads() {
  // Handle image upload
  const imageUpload = document.getElementById("image-upload")
  const imageUploadArea = document.querySelector("#image-tab .file-upload-area")

  if (imageUpload && imageUploadArea) {
    setupFileUpload(imageUpload, imageUploadArea, "image")
  }

  // Handle audio upload
  const audioUpload = document.getElementById("audio-upload")
  const audioUploadArea = document.querySelector("#audio-tab .file-upload-area")

  if (audioUpload && audioUploadArea) {
    setupFileUpload(audioUpload, audioUploadArea, "audio")
  }
}

function setupFileUpload(fileInput, uploadArea, type) {
  fileInput.addEventListener("change", (e) => {
    handleFileSelection(e.target.files[0], uploadArea, type)
  })

  // Drag and drop functionality
  uploadArea.addEventListener("dragover", (e) => {
    e.preventDefault()
    uploadArea.style.borderColor = "var(--primary-blue)"
    uploadArea.style.background = "rgba(59, 130, 246, 0.1)"
  })

  uploadArea.addEventListener("dragleave", (e) => {
    e.preventDefault()
    uploadArea.style.borderColor = "var(--border-color)"
    uploadArea.style.background = "var(--bg-secondary)"
  })

  uploadArea.addEventListener("drop", (e) => {
    e.preventDefault()
    uploadArea.style.borderColor = "var(--border-color)"
    uploadArea.style.background = "var(--bg-secondary)"

    const files = e.dataTransfer.files
    if (files.length > 0) {
      fileInput.files = files
      handleFileSelection(files[0], uploadArea, type)
    }
  })
}

function handleFileSelection(file, uploadArea, type) {
  if (!file) return

  const uploadContent = uploadArea.querySelector(".upload-content")
  const maxSize = type === "audio" ? 25 : 10 // MB
  const fileSize = (file.size / 1024 / 1024).toFixed(2)

  // Validate file size
  if (file.size > maxSize * 1024 * 1024) {
    uploadContent.innerHTML = `
            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
            <h3>File Too Large</h3>
            <p>Maximum size: ${maxSize}MB. Your file: ${fileSize}MB</p>
        `
    showNotification(`File too large. Maximum size is ${maxSize}MB.`, "error")
    return
  }

  // Validate file type
  const validTypes =
    type === "audio"
      ? ["audio/mpeg", "audio/mp3", "audio/wav", "audio/mp4", "audio/m4a"]
      : ["image/jpeg", "image/jpg", "image/png", "image/webp"]

  if (!validTypes.includes(file.type)) {
    uploadContent.innerHTML = `
            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
            <h3>Invalid File Type</h3>
            <p>Please upload a valid ${type} file</p>
        `
    showNotification(`Invalid file type. Please upload a valid ${type} file.`, "error")
    return
  }

  // File is valid
  uploadContent.innerHTML = `
        <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
        <h3>File Ready</h3>
        <p>${file.name} (${fileSize} MB)</p>
    `
  uploadArea.style.borderColor = "var(--accent-green)"
  showNotification("File uploaded successfully!", "success")
}

// Form Validation
function setupFormValidation() {
  const forms = document.querySelectorAll(".analyzer-form")

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const submitBtn = form.querySelector(".btn-primary")
      const originalText = submitBtn.innerHTML

      // Show loading state
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing...'
      submitBtn.disabled = true

      // Validate form
      const type = form.querySelector('input[name="type"]').value

      if (type === "text") {
        const textContent = form.querySelector('textarea[name="content"]').value.trim()
        if (!textContent) {
          e.preventDefault()
          showNotification("Please enter some text to analyze.", "error")
          resetSubmitButton(submitBtn, originalText)
          return
        }
      } else if (type === "image" || type === "audio") {
        const fileInput = form.querySelector(`input[name="${type}"]`)
        if (!fileInput.files || fileInput.files.length === 0) {
          e.preventDefault()
          showNotification(`Please upload a ${type} file for analysis.`, "error")
          resetSubmitButton(submitBtn, originalText)
          return
        }
      }

      // Reset button after timeout (in case of server errors)
      setTimeout(() => {
        resetSubmitButton(submitBtn, originalText)
      }, 30000)
    })
  })
}

function resetSubmitButton(button, originalText) {
  button.innerHTML = originalText
  button.disabled = false
}

// Animate Confidence Circle
function animateConfidenceCircle(percentage) {
  const circle = document.querySelector(".progress-ring-circle")
  if (!circle) return

  const radius = 54
  const circumference = 2 * Math.PI * radius
  const offset = circumference - (percentage / 100) * circumference

  // Set initial state
  circle.style.strokeDasharray = circumference
  circle.style.strokeDashoffset = circumference

  // Animate to final state
  setTimeout(() => {
    circle.style.strokeDashoffset = offset
  }, 100)

  // Animate the percentage counter
  const percentageElement = document.querySelector(".percentage")
  if (percentageElement) {
    let current = 0
    const increment = percentage / 50
    const timer = setInterval(() => {
      current += increment
      if (current >= percentage) {
        current = percentage
        clearInterval(timer)
      }
      percentageElement.textContent = Math.round(current) + "%"
    }, 20)
  }
}

// Notification System
function showNotification(message, type = "info") {
  // Remove existing notifications
  const existingNotification = document.querySelector(".notification")
  if (existingNotification) {
    existingNotification.remove()
  }

  // Create notification
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 16px 24px;
        border-radius: 12px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 400px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    `

  // Set background color based on type
  switch (type) {
    case "success":
      notification.style.background = "linear-gradient(135deg, #10b981, #059669)"
      break
    case "error":
      notification.style.background = "linear-gradient(135deg, #ef4444, #dc2626)"
      break
    case "warning":
      notification.style.background = "linear-gradient(135deg, #f59e0b, #d97706)"
      break
    default:
      notification.style.background = "linear-gradient(135deg, #3b82f6, #2563eb)"
  }

  notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-triangle" : "info-circle"}"></i>
            <span>${message}</span>
        </div>
    `

  document.body.appendChild(notification)

  // Animate in
  setTimeout(() => {
    notification.style.transform = "translateX(0)"
  }, 10)

  // Auto remove after 5 seconds
  setTimeout(() => {
    notification.style.transform = "translateX(100%)"
    setTimeout(() => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification)
      }
    }, 300)
  }, 5000)
}

// Add CSS animations
const style = document.createElement("style")
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    @keyframes pulse {
        0%, 100% { 
            transform: scale(1);
            opacity: 1;
        }
        50% { 
            transform: scale(1.05);
            opacity: 0.8;
        }
    }
    
    .notification {
        backdrop-filter: blur(10px);
    }
`
document.head.appendChild(style)



