import React, { createContext, useContext, useState, useEffect } from 'react';

const ThemeContext = createContext();

export const ThemeProvider = ({ children }) => {
    // Load saved preferences or default
    const [theme, setTheme] = useState(localStorage.getItem('theme') || 'light'); // 'light' or 'dark'
    const [colorMode, setColorMode] = useState(localStorage.getItem('colorMode') || 'blue'); // 'blue', 'purple', 'green'

    useEffect(() => {
        const root = window.document.documentElement;

        // Remove old classes
        root.classList.remove('dark', 'light');
        root.classList.add(theme);

        // Save to local storage
        localStorage.setItem('theme', theme);
        localStorage.setItem('colorMode', colorMode);

        // Apply color variables (CSS variables concept)
        // For simplicity, we might just use classes, but variables are better for general overrides
        // In a real app we'd map colorMode to specific hue values
        // root.style.setProperty('--primary-color', ...);

    }, [theme, colorMode]);

    const toggleTheme = () => {
        setTheme(prev => prev === 'light' ? 'dark' : 'light');
    };

    return (
        <ThemeContext.Provider value={{ theme, setTheme, toggleTheme, colorMode, setColorMode }}>
            {children}
        </ThemeContext.Provider>
    );
};

export const useTheme = () => useContext(ThemeContext);
