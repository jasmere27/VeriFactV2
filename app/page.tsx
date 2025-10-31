"use client"

import { useState, useEffect } from "react"
import { ChevronRight, Upload, Brain, Eye, Mic, Shield, CheckCircle, ArrowRight, Play } from "lucide-react"

export default function LandingPage() {
  const [isVisible, setIsVisible] = useState(false)

  useEffect(() => {
    setIsVisible(true)
  }, [])

  return (
    <div className="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 text-white overflow-hidden">
      {/* Animated Background Elements */}
      <div className="fixed inset-0 overflow-hidden pointer-events-none">
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div className="absolute top-1/2 left-1/2 w-64 h-64 bg-cyan-500/5 rounded-full blur-2xl animate-bounce"></div>
      </div>

      {/* Navigation */}
      <nav className="relative z-50 px-6 py-4">
        <div className="max-w-7xl mx-auto flex items-center justify-between">
          <div className="flex items-center space-x-2">
            <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
              <Shield className="w-5 h-5 text-white" />
            </div>
            <span className="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
              VeriFact
            </span>
          </div>

          <div className="hidden md:flex items-center space-x-8">
            <a href="#features" className="text-gray-300 hover:text-white transition-colors">
              Features
            </a>
            <a href="#how-it-works" className="text-gray-300 hover:text-white transition-colors">
              How It Works
            </a>
            <a href="#pricing" className="text-gray-300 hover:text-white transition-colors">
              Pricing
            </a>
            <button className="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-2 rounded-full hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
              Get Started
            </button>
          </div>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative z-10 px-6 py-20">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div className={`space-y-8 ${isVisible ? "animate-fade-in-up" : "opacity-0"}`}>
              <div className="space-y-4">
                <h1 className="text-5xl lg:text-7xl font-bold leading-tight">
                  <span className="bg-gradient-to-r from-white via-blue-200 to-purple-200 bg-clip-text text-transparent">
                    Verify Truth,
                  </span>
                  <br />
                  <span className="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                    Fight Misinformation
                  </span>
                </h1>
                <p className="text-xl text-gray-300 max-w-2xl leading-relaxed">
                  AI-powered detection to ensure the authenticity of news and media. Protect yourself from fake news
                  with cutting-edge technology.
                </p>
              </div>

              <div className="flex flex-col sm:flex-row gap-4">
                <button className="group bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-4 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/25">
                  <span className="flex items-center justify-center space-x-2">
                    <Play className="w-5 h-5" />
                    <span>Try Demo</span>
                    <ChevronRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                  </span>
                </button>
                <button className="group border-2 border-gray-600 hover:border-blue-500 px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-300 hover:bg-blue-500/10">
                  <span className="flex items-center justify-center space-x-2">
                    <Upload className="w-5 h-5" />
                    <span>Upload News</span>
                  </span>
                </button>
              </div>

              {/* Trusted By Section */}
              <div className="pt-8">
                <p className="text-gray-400 text-sm mb-6">Trusted by leading organizations</p>
                <div className="flex items-center space-x-8 opacity-60">
                  <div className="text-2xl font-bold">CNN</div>
                  <div className="text-2xl font-bold">BBC</div>
                  <div className="text-2xl font-bold">Reuters</div>
                  <div className="text-2xl font-bold">AP</div>
                  <div className="text-2xl font-bold">NPR</div>
                </div>
              </div>
            </div>

            {/* 3D Illustration */}
            <div className={`relative ${isVisible ? "animate-fade-in-right" : "opacity-0"}`}>
              <div className="relative w-full max-w-lg mx-auto">
                {/* Main 3D Brain/AI Chip */}
                <div className="relative">
                  <div className="w-80 h-80 mx-auto relative">
                    {/* Outer glow */}
                    <div className="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-600 rounded-3xl blur-2xl opacity-30 animate-pulse"></div>

                    {/* Main container */}
                    <div className="relative w-full h-full bg-gradient-to-br from-gray-800 via-gray-900 to-black rounded-3xl border border-gray-700 overflow-hidden">
                      {/* Circuit pattern overlay */}
                      <div className="absolute inset-0 opacity-20">
                        <svg className="w-full h-full" viewBox="0 0 100 100">
                          <defs>
                            <pattern id="circuit" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                              <path d="M 10,0 L 10,10 L 0,10" stroke="currentColor" strokeWidth="0.5" fill="none" />
                              <circle cx="10" cy="10" r="1" fill="currentColor" />
                            </pattern>
                          </defs>
                          <rect width="100" height="100" fill="url(#circuit)" className="text-blue-400" />
                        </svg>
                      </div>

                      {/* Central brain/chip */}
                      <div className="absolute inset-8 bg-gradient-to-br from-blue-600/20 to-purple-600/20 rounded-2xl border border-blue-500/30 flex items-center justify-center">
                        <Brain className="w-24 h-24 text-blue-400 animate-pulse" />
                      </div>

                      {/* Floating elements */}
                      <div className="absolute top-4 right-4 w-3 h-3 bg-blue-400 rounded-full animate-ping"></div>
                      <div className="absolute bottom-6 left-6 w-2 h-2 bg-purple-400 rounded-full animate-ping delay-500"></div>
                      <div className="absolute top-1/2 left-4 w-2 h-2 bg-cyan-400 rounded-full animate-ping delay-1000"></div>
                    </div>
                  </div>

                  {/* Orbiting elements */}
                  <div className="absolute inset-0 animate-spin-slow">
                    <div className="absolute top-0 left-1/2 w-4 h-4 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transform -translate-x-1/2 -translate-y-8"></div>
                  </div>
                  <div className="absolute inset-0 animate-spin-reverse">
                    <div className="absolute bottom-0 right-1/4 w-3 h-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transform translate-y-8"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* How It Works Section */}
      <section id="how-it-works" className="relative z-10 px-6 py-20">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl lg:text-5xl font-bold mb-6">
              <span className="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                How It Works
              </span>
            </h2>
            <p className="text-xl text-gray-300 max-w-3xl mx-auto">
              Our advanced AI system analyzes content in three simple steps to deliver accurate results
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                step: "01",
                title: "Upload Content",
                description: "Submit text, images, or audio content for analysis",
                icon: Upload,
                color: "from-blue-500 to-cyan-500",
              },
              {
                step: "02",
                title: "AI Analyzes",
                description: "Our neural networks process and verify the content",
                icon: Brain,
                color: "from-purple-500 to-pink-500",
              },
              {
                step: "03",
                title: "Get Result",
                description: "Receive detailed authenticity report with confidence score",
                icon: CheckCircle,
                color: "from-green-500 to-emerald-500",
              },
            ].map((item, index) => (
              <div key={index} className="relative group">
                <div className="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 hover:border-blue-500/50 transition-all duration-300 transform hover:scale-105">
                  <div
                    className={`w-16 h-16 bg-gradient-to-r ${item.color} rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform`}
                  >
                    <item.icon className="w-8 h-8 text-white" />
                  </div>
                  <div className="text-sm text-gray-400 font-mono mb-2">STEP {item.step}</div>
                  <h3 className="text-2xl font-bold mb-4">{item.title}</h3>
                  <p className="text-gray-300 leading-relaxed">{item.description}</p>
                </div>

                {/* Connection line */}
                {index < 2 && (
                  <div className="hidden md:block absolute top-1/2 -right-4 w-8 h-0.5 bg-gradient-to-r from-blue-500 to-purple-500 transform -translate-y-1/2">
                    <ArrowRight className="absolute -right-2 -top-2 w-4 h-4 text-purple-400" />
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section id="features" className="relative z-10 px-6 py-20">
        <div className="max-w-7xl mx-auto">
          <div className="text-center mb-16">
            <h2 className="text-4xl lg:text-5xl font-bold mb-6">
              <span className="bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                Powerful Features
              </span>
            </h2>
            <p className="text-xl text-gray-300 max-w-3xl mx-auto">
              Advanced AI capabilities to detect misinformation across all media types
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                title: "Text Analysis",
                description:
                  "Advanced NLP algorithms analyze linguistic patterns, fact-check claims, and verify sources in real-time.",
                icon: Eye,
                features: ["Source verification", "Sentiment analysis", "Fact checking", "Language patterns"],
                color: "from-blue-500 to-cyan-500",
              },
              {
                title: "Image OCR Detection",
                description:
                  "Extract and verify text from images, detect manipulated content, and analyze visual authenticity.",
                icon: Eye,
                features: ["Text extraction", "Manipulation detection", "Reverse image search", "Metadata analysis"],
                color: "from-purple-500 to-pink-500",
              },
              {
                title: "Audio Fact-Check",
                description:
                  "Transcribe audio content, analyze speech patterns, and verify spoken claims against trusted sources.",
                icon: Mic,
                features: ["Speech-to-text", "Voice analysis", "Claim verification", "Audio forensics"],
                color: "from-green-500 to-emerald-500",
              },
            ].map((feature, index) => (
              <div key={index} className="group">
                <div className="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 hover:border-blue-500/50 transition-all duration-300 transform hover:scale-105 h-full">
                  <div
                    className={`w-16 h-16 bg-gradient-to-r ${feature.color} rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform`}
                  >
                    <feature.icon className="w-8 h-8 text-white" />
                  </div>

                  <h3 className="text-2xl font-bold mb-4">{feature.title}</h3>
                  <p className="text-gray-300 mb-6 leading-relaxed">{feature.description}</p>

                  <ul className="space-y-2">
                    {feature.features.map((item, i) => (
                      <li key={i} className="flex items-center space-x-2 text-sm text-gray-400">
                        <CheckCircle className="w-4 h-4 text-green-400" />
                        <span>{item}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="relative z-10 px-6 py-20">
        <div className="max-w-4xl mx-auto text-center">
          <div className="bg-gradient-to-r from-blue-600/20 to-purple-600/20 backdrop-blur-sm border border-blue-500/30 rounded-3xl p-12">
            <h2 className="text-4xl lg:text-5xl font-bold mb-6">
              <span className="bg-gradient-to-r from-white to-blue-200 bg-clip-text text-transparent">
                Ready to Fight Misinformation?
              </span>
            </h2>
            <p className="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
              Join thousands of users who trust VeriFact to verify news and protect against fake information.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <button className="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-4 rounded-2xl font-semibold text-lg hover:from-blue-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-105">
                Start Free Trial
              </button>
              <button className="border-2 border-gray-600 hover:border-blue-500 px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-300 hover:bg-blue-500/10">
                View Pricing
              </button>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="relative z-10 px-6 py-12 border-t border-gray-800">
        <div className="max-w-7xl mx-auto">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div className="flex items-center space-x-2 mb-4 md:mb-0">
              <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <Shield className="w-5 h-5 text-white" />
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                VeriFact
              </span>
            </div>
            <div className="text-gray-400 text-sm">
              © 2024 VeriFact. All rights reserved. Fighting misinformation with AI.
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}
