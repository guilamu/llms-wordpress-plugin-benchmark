const fs = require('fs');
const path = require('path');

const dir = 'c:/Temp/tests';

// Read mapping
const text = fs.readFileSync(path.join(dir, 'test.txt'), 'utf8');
const lines = text.split('\n');
const mapping = {};
for (let line of lines) {
  line = line.trim();
  if (!line) continue;
  const parts = line.split('#');
  if (parts.length === 2) {
    const filename = parts[0].trim();
    const modelName = parts[1].trim();
    mapping[filename] = modelName;
  }
}

const result = [];
for (let i = 1; i <= 8; i++) {
  const filename = `${i}.json`;
  if (mapping[filename]) {
    const filepath = path.join(dir, filename);
    const content = fs.readFileSync(filepath, 'utf8');
    const data = JSON.parse(content);
    data.model = mapping[filename];
    result.push(data);
  }
}

fs.writeFileSync(path.join(dir, 'recapitulatif.json'), JSON.stringify(result, null, 2));
console.log('Successfully created recapitulatif.json');
