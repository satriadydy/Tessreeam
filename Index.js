const express = require('express');
const axios = require('axios');
const cheerio = require('cheerio');
const cors = require('cors');

const app = express();
const port = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Basic route
app.get('/', (req, res) => {
  res.json({ 
    message: 'Alfa Learning Scraper API',
    endpoints: {
      'POST /api/getName': 'Get name by NIK',
      'GET /api/getName?nik=123': 'Get name by NIK (GET method)'
    }
  });
});

// Main scraping function
async function getNameFromAlfa(nik) {
  try {
    // Create axios instance with session handling
    const axiosInstance = axios.create({
      timeout: 10000,
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
      }
    });

    console.log('Attempting to login...');
    
    // Step 1: Login
    const loginResponse = await axiosInstance.post('https://alfalearning.sat.co.id/login/index.php', 
      new URLSearchParams({
        'username': '22088181',
        'password': 'A12345678'
      }),
      {
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        }
      }
    );

    // Extract cookies from login response
    const cookies = loginResponse.headers['set-cookie'];
    
    console.log('Login successful, searching for NIK:', nik);

    // Step 2: Search with cookies
    const searchResponse = await axiosInstance.get(
      `https://alfalearning.sat.co.id/search/index.php?context=1&q=${nik}`,
      {
        headers: {
          'Cookie': cookies ? cookies.join('; ') : ''
        }
      }
    );

    console.log('Search completed, parsing HTML...');

    // Step 3: Parse HTML
    const $ = cheerio.load(searchResponse.data);
    
    // Try multiple strategies to find the name
    let name = null;

    // Strategy 1: Look for table rows containing NIK
    $('tr').each((i, row) => {
      const rowText = $(row).text();
      if (rowText.includes(nik)) {
        const cells = $(row).find('td');
        cells.each((j, cell) => {
          const cellText = $(cell).text().trim();
          if (cellText && cellText !== nik && cellText.length > 3 && /^[A-Za-z\s]+$/.test(cellText)) {
            name = cellText;
            return false;
          }
        });
        if (name) return false;
      }
    });

    // Strategy 2: Look for any element containing NIK and extract nearby text
    if (!name) {
      $('*').each((i, elem) => {
        const text = $(elem).text();
        if (text.includes(nik)) {
          const siblings = $(elem).siblings();
          siblings.each((j, sibling) => {
            const siblingText = $(sibling).text().trim();
            if (siblingText && siblingText !== nik && siblingText.length > 3 && /^[A-Za-z\s]+$/.test(siblingText)) {
              name = siblingText;
              return false;
            }
          });
          if (name) return false;
        }
      });
    }

    // Strategy 3: Regex search in HTML
    if (!name) {
      const htmlContent = searchResponse.data;
      const patterns = [
        new RegExp(`${nik}[^>]*>\\s*([A-Z][a-z]+\\s+[A-Z][a-z]+)`, 'i'),
        new RegExp(`nama[^>]*>\\s*([A-Z][a-z]+\\s+[A-Z][a-z]+)`, 'i'),
        new RegExp(`([A-Z][a-z]+\\s+[A-Z][a-z]+)[^>]*${nik}`, 'i')
      ];
      
      for (const pattern of patterns) {
        const match = htmlContent.match(pattern);
        if (match && match[1]) {
          name = match[1].trim();
          break;
        }
      }
    }

    if (name) {
      console.log('Name found:', name);
      return { success: true, name: name, nik: nik };
    } else {
      console.log('Name not found for NIK:', nik);
      return { error: 'Name not found for NIK: ' + nik };
    }

  } catch (error) {
    console.error('Error in getNameFromAlfa:', error.message);
    return { error: 'Failed to fetch data: ' + error.message };
  }
}

// API Routes
app.post('/api/getName', async (req, res) => {
  const { nik } = req.body;
  
  if (!nik) {
    return res.status(400).json({ error: 'NIK is required' });
  }
  
  const result = await getNameFromAlfa(nik);
  res.json(result);
});

app.get('/api/getName', async (req, res) => {
  const { nik } = req.query;
  
  if (!nik) {
    return res.status(400).json({ error: 'NIK is required' });
  }
  
  const result = await getNameFromAlfa(nik);
  res.json(result);
});

// Error handling
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Something went wrong!' });
});

app.listen(port, () => {
  console.log(`🚀 Server running on port ${port}`);
  console.log(`📡 API available at: http://localhost:${port}`);
});
