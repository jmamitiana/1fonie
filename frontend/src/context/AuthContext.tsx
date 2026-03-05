'use client';

import { useEffect, useState, createContext, useContext, ReactNode } from 'react';
import { useAuthStore } from '@/context/authStore';
import { User } from '@/types';

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  register: (data: any) => Promise<void>;
  logout: () => Promise<void>;
  checkAuth: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const {
    user,
    isAuthenticated,
    isLoading,
    login: storeLogin,
    register: storeRegister,
    logout: storeLogout,
    checkAuth: storeCheckAuth,
  } = useAuthStore();

  useEffect(() => {
    storeCheckAuth();
  }, []);

  const login = async (email: string, password: string) => {
    await storeLogin(email, password);
  };

  const register = async (data: any) => {
    await storeRegister(data);
  };

  const logout = async () => {
    await storeLogout();
  };

  const checkAuth = async () => {
    await storeCheckAuth();
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isAuthenticated,
        isLoading,
        login,
        register,
        logout,
        checkAuth,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
