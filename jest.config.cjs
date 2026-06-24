module.exports = {
  testEnvironment: 'node',
  roots: ['<rootDir>/resources/specs', '<rootDir>/tests/Specs'],
  testMatch: ['**/__tests__/**/*.js', '**/?(*.)+(spec|test).js'],
  transform: {
    '^.+\\.jsx?$': 'babel-jest',
  },
  collectCoverageFrom: [
    'resources/**/*.js',
    '!resources/**/node_modules/**',
  ],
};
