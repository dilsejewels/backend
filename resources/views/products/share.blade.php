<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $productName }}</title>

  <!-- ✅ Open Graph Meta Tags (Facebook, LinkedIn, etc.) -->
  <meta property="og:title" content="{{ $productName }}" />
  <meta property="og:description" content="{{ $productDescription }}" />
  @if(!empty($imageUrl))
    <meta property="og:image" content="{{ $imageUrl }}" />
    <meta property="og:image:secure_url" content="{{ $imageUrl }}" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="1200" />
  @endif
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:type" content="product" />

  <!-- ✅ Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $productName }}">
  <meta name="twitter:description" content="{{ $productDescription }}">
  @if(!empty($imageUrl))
    <meta name="twitter:image" content="{{ $imageUrl }}">
  @endif

  <style>
    body {
      font-family: system-ui, sans-serif;
      padding: 2rem;
      text-align: center;
      background-color: #fafafa;
      margin: 0;
    }

    .share-container {
      max-width: 700px;
      margin: 0 auto;
      background: #fff;
      padding: 1.5rem;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .shareheading {
      font-size: 1.6rem;
      font-weight: 600;
      margin-bottom: 1rem;
      color: #222;
    }

    .shareimg {
      display: block;
      width: 100%;
      max-width: 600px;
      height: 600px; /* ✅ fixed height for consistent aspect ratio */
      object-fit: contain; /* ✅ ensures full image is visible */
      border-radius: 10px;
      background-color: #fff;
      margin: 0 auto;
    }

    .sharepera {
      margin-top: 1rem;
      color: #444;
      line-height: 1.6;
      font-size: 1rem;
    }

    @media (max-width: 768px) {
      body {
        padding: 1rem;
      }
      .shareimg {
        max-width: 100%;
        height: auto;
      }
    }
  </style>
</head>
<body>
  <div class="share-container">
    <h1 class="shareheading">{{ $productName }}</h1>
    @if(!empty($imageUrl))
      <img class="shareimg" src="{{ $imageUrl }}" alt="{{ $productName }}">
    @endif
    <p class="sharepera">{{ $productDescription }}</p>
  </div>
</body>
</html>
