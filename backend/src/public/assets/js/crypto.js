// Hàm mã hóa password bằng SHA-256
export async function sha256(message) {
    // Chuyển đổi message thành Uint8Array
    const msgBuffer = new TextEncoder().encode(message);
    
    // Mã hóa message
    const hashBuffer = await crypto.subtle.digest("SHA-256", msgBuffer);
    
    // Chuyển đổi ArrayBuffer thành Array
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    
    // Chuyển đổi bytes thành hex string
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, "0")).join("");
    
    return hashHex;
} 